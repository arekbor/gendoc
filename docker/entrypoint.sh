#!/bin/bash
composer install -n
bin/console cache:clear
bin/console importmap:install
bin/console asset-map:compile

setfacl -dR -m u:www-data:rwX -m u:$(whoami):rwX var
setfacl -R -m u:www-data:rwX -m u:$(whoami):rwX var

exec apache2-foreground