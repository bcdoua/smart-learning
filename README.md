# Smart Learning

Plateforme d'apprentissage en ligne (Symfony 7.3, PHP 8.4, MySQL 8.4).

## Démarrage

```bash
cp .env.compose.example .env && docker compose up -d --build
```

Appliquer les migrations (première fois uniquement) :

```bash
docker compose exec api php bin/console doctrine:migrations:migrate --no-interaction
```

Vérifier : `curl http://localhost:8080/health` renvoie `{"status":"ok"}`.

## Contrôles

- `./scripts/check-project.sh` : fichiers requis, aucun `.env` ni `.pem` suivi.
- `php vendor/bin/phpunit` : tests (exécutés par la CI sur chaque pull request).

## Mise en service (staging)

```bash
cp .env.staging.example .env.staging
./scripts/deploy.sh v1.1.1
```

L'image `ghcr.io/bcdoua/smart-learning:<tag>` est publiée par la CI à chaque tag `vX.Y.Z` et jamais reconstruite.

## Retour arrière

```bash
./scripts/deploy.sh v1.0.0
```

Voir `docs/incident.md` pour le déroulé de la démonstration.
