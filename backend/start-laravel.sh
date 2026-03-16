#!/bin/bash
# Laravel Server Starter for Git Bash
echo "Starting Laravel server with Laragon PHP..."
cd "$(dirname "$0")"
"C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe" artisan serve --host=127.0.0.1 --port=8000
