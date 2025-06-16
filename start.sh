#!/bin/sh
echo "Starting Symfony backend for fixmymind.org..."
php -S 0.0.0.0:$PORT -t public
