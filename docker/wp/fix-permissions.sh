#!/bin/sh
set -eu

ensure_ai1wm_permissions() {
  mkdir -p /var/www/html/wp-content/ai1wm-backups

  if [ -d /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage ]; then
    chown -R 33:33 /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage
    find /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage -type d -exec chmod 777 {} \;
    find /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage -type f -exec chmod 666 {} \;
  fi

  chown -R 33:33 /var/www/html/wp-content/ai1wm-backups
  find /var/www/html/wp-content/ai1wm-backups -type d -exec chmod 777 {} \;
  find /var/www/html/wp-content/ai1wm-backups -type f -exec chmod 666 {} \;
}

mkdir -p /var/www/html/wp-content
mkdir -p /var/www/html/wp-content/plugins
mkdir -p /var/www/html/wp-content/upgrade
mkdir -p /var/www/html/wp-content/uploads
mkdir -p /var/www/html/wp-content/ai1wm-backups

chown -R 33:33 /var/www/html/wp-content/plugins
chown -R 33:33 /var/www/html/wp-content/upgrade
chown -R 33:33 /var/www/html/wp-content/uploads
chown -R 33:33 /var/www/html/wp-content/ai1wm-backups
find /var/www/html/wp-content/plugins -type d -exec chmod 775 {} \;
find /var/www/html/wp-content/plugins -type f -exec chmod 664 {} \;
find /var/www/html/wp-content/upgrade -type d -exec chmod 775 {} \;
find /var/www/html/wp-content/upgrade -type f -exec chmod 664 {} \;
find /var/www/html/wp-content/uploads -type d -exec chmod 775 {} \;
find /var/www/html/wp-content/uploads -type f -exec chmod 664 {} \;
find /var/www/html/wp-content/ai1wm-backups -type d -exec chmod 775 {} \;
find /var/www/html/wp-content/ai1wm-backups -type f -exec chmod 664 {} \;

ensure_ai1wm_permissions
