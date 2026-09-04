#!/bin/sh
set -eu

if [ ! -f /var/www/html/index.php ]; then
  echo "Bootstrapping WordPress core into /var/www/html..."
  cp -a /usr/src/wordpress/. /var/www/html/
fi

/usr/local/bin/fix-permissions.sh
/usr/local/bin/sync-local-plugins.sh

exec docker-entrypoint.sh apache2-foreground
