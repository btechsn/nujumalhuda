#!/bin/sh
set -eu

SECRET="${MEDIAMTX_WEBHOOK_SECRET:-}"
if [ -z "$SECRET" ]; then
    echo "MEDIAMTX_WEBHOOK_SECRET est vide : les hooks et l'auth HTTP seront refuses par Laravel." >&2
fi

sed "s|__WEBHOOK_SECRET__|${SECRET}|g" /mediamtx.yml.template > /tmp/mediamtx.yml
exec /usr/local/bin/mediamtx /tmp/mediamtx.yml
