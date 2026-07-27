#!/usr/bin/env sh
set -eu

printf "[post-merge] Repository verification...\n"
npm run -s verify
