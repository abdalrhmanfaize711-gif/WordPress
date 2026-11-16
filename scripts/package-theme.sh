#!/usr/bin/env sh
set -eu
ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT"

test -f methaq-vw-restaurant/style.css
test -f methaq-vw-restaurant/functions.php
test -f methaq-vw-restaurant/front-page.php
grep -q '^Template: vw-restaurant-lite$' methaq-vw-restaurant/style.css
php -l methaq-vw-restaurant/functions.php
php -l methaq-vw-restaurant/front-page.php

mkdir -p dist
rm -f dist/methaq-vw-restaurant.zip
zip -r dist/methaq-vw-restaurant.zip methaq-vw-restaurant -x '*/.DS_Store'
unzip -t dist/methaq-vw-restaurant.zip
unzip -Z1 dist/methaq-vw-restaurant.zip | grep -q '^methaq-vw-restaurant/style.css$'
printf '\nCreated: %s\n' "$ROOT/dist/methaq-vw-restaurant.zip"
