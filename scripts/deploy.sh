#!/usr/bin/env bash
# Usage : ./scripts/deploy.sh v1.0.0
set -euo pipefail
tag="${1:?usage: deploy.sh vX.Y.Z}"
printf 'IMAGE_TAG=%s\n' "$tag" > .env.deploy
docker compose --env-file .env.deploy -f compose.staging.yaml pull api
docker compose --env-file .env.deploy -f compose.staging.yaml up -d
./scripts/wait-healthy.sh
