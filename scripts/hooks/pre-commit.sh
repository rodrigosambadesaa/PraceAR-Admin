#!/usr/bin/env sh
set -eu

printf "[pre-commit] Repository verification...\n"
npm run -s verify

printf "[pre-commit] Secret scan on staged files...\n"
node scripts/scan-secrets-staged.mjs
