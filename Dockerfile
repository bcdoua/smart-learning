FROM php:8.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod \
    APP_DEBUG=0

# Extensions PHP requises par le projet (MySQL, intl, zip, gd pour dompdf)
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      git unzip libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip gd opcache \
 && rm -rf /var/lib/apt/lists/*

# Configuration PHP de production : les erreurs vont dans les journaux, pas dans la page
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php-env.ini /usr/local/etc/php/conf.d/zz-env.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Apache : port 8080 (un utilisateur non root ne peut pas ouvrir le port 80)
RUN a2enmod rewrite \
 && a2disconf other-vhosts-access-log \
 && sed -ri 's/Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
 && rm -f /etc/apache2/sites-enabled/000-default.conf
COPY docker/apache.conf /etc/apache2/sites-enabled/app.conf

WORKDIR /var/www/html

# Dépendances d'abord : cette couche ne change presque jamais (cache)
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# Code applicatif ensuite
COPY bin ./bin
COPY config ./config
COPY migrations ./migrations
COPY public ./public
COPY src ./src
COPY templates ./templates
COPY translations ./translations

# .env vide : Symfony exige que le fichier existe, les vraies valeurs viennent de l'environnement
RUN touch .env \
 && composer dump-autoload --no-dev --no-scripts --classmap-authoritative \
 && mkdir -p var/cache var/log public/uploads \
 && chown -R www-data:www-data var public/uploads /var/run/apache2 /var/lock/apache2 /var/log/apache2

USER www-data
EXPOSE 8080

# Le test lit la réponse : il exige {"status":"ok"}, pas seulement une réponse HTTP
HEALTHCHECK --interval=10s --timeout=3s --start-period=20s --retries=3 \
  CMD php -r 'exit((json_decode((string)@file_get_contents("http://127.0.0.1:8080/health"),true)["status"] ?? "")==="ok"?0:1);'
