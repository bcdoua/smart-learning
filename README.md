# Smart Learning

Plateforme d'apprentissage en ligne (Symfony 7.3, PHP 8.4, MySQL 8.4).

## Démarrage

```bash
cp .env.compose.example .env
docker compose up -d --build
docker compose exec api php bin/console doctrine:migrations:migrate --no-interaction
curl http://localhost:8080/health
```

## Architecture

- `api` : application Symfony (Apache, port 8080, utilisateur non root)
- `db` : MySQL 8.4, volume nommé `dbdata`, port non publié
