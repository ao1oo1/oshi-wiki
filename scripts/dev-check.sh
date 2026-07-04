#!/usr/bin/env bash
set -e

echo "== PHP version =="
php -v

echo "== Composer validate =="
composer validate --no-check-publish

echo "== Laravel version =="
php artisan --version

echo "== Migration status =="
php artisan migrate:status

echo "== Pint test =="
./vendor/bin/pint --test

echo "== Tests =="
php artisan test
