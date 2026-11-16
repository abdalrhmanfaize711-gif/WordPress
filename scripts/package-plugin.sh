#!/usr/bin/env sh
set -eu
ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT"

test -f methaq-restaurant/methaq-restaurant.php
test -f methaq-restaurant/assets/css/restaurant.css
test -f methaq-restaurant/assets/js/restaurant.js

php -l methaq-restaurant/methaq-restaurant.php
mkdir -p dist
rm -f dist/methaq-restaurant.zip
zip -r dist/methaq-restaurant.zip methaq-restaurant -x '*/.DS_Store'
unzip -t dist/methaq-restaurant.zip
unzip -Z1 dist/methaq-restaurant.zip | grep -q '^methaq-restaurant/methaq-restaurant.php$'

printf '\nCreated: %s\n' "$ROOT/dist/methaq-restaurant.zip"
