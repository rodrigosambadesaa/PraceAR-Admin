#!/usr/bin/env sh
set -eu

printf "[pre-commit] TypeScript build...\n"
npm run -s build

printf "[pre-commit] Secret scan on staged files...\n"
node scripts/scan-secrets-staged.mjs
