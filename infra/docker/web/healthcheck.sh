#!/bin/sh
# Healthcheck: verify the Next.js server is responding
wget -qO- http://localhost:3000/api/health 2>/dev/null | grep -q "ok" && exit 0 || exit 1
