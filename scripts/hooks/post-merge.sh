#!/usr/bin/env sh
set -eu

printf "[post-merge] TypeScript build...\n"
npm run -s build
