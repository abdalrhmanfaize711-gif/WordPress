#!/usr/bin/env sh
set -eu
ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT"
mkdir -p dist
rm -f dist/methaq-restaurant.zip
zip -r dist/methaq-restaurant.zip methaq-restaurant -x '*/.DS_Store'
unzip -t dist/methaq-restaurant.zip
printf '\nCreated: %s\n' "$ROOT/dist/methaq-restaurant.zip"
