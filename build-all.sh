#!/bin/bash

COLOR_RED='\033[0;31m'
COLOR_YELLOW='\033[1;33m'
COLOR_GREEN='\033[1;32m'
COLOR_END='\033[0m'

echo
echo -e "${COLOR_YELLOW}> Building backend${COLOR_END}"
cd api/
composer install
composer update
sudo chown -R www-data:www-data public/documents/

echo
echo -e "${COLOR_YELLOW}> Building frontend${COLOR_END}"
cd ../webapp
yarn install
yarn build

echo
echo -e "${COLOR_GREEN}[+] Done${COLOR_END}"
echo 
cd ..
