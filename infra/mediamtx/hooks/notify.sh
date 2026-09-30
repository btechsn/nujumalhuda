#!/bin/sh
curl -fsS -m 5 -X POST \
  -H "Content-Type: application/json" \
  -H "X-Webhook-Secret: ${MEDIAMTX_WEBHOOK_SECRET}" \
  -d "{\"event\":\"$1\",\"path\":\"${MTX_PATH}\"}" \
  http://nginx/api/v1/webhooks/mediamtx || true
