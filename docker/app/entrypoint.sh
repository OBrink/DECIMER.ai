#!/bin/sh
set -e

php-fpm && supervisord
