#!/bin/sh
set -eu

live_root="/var/www/local-plugins-live"
plugins_root="/var/www/html/wp-content/plugins"

sync_once() {
  mkdir -p "$plugins_root"

  if [ ! -d "$live_root" ]; then
    return 0
  fi

  for existing in "$plugins_root"/*; do
    if [ ! -L "$existing" ]; then
      continue
    fi

    target=$(readlink "$existing" || true)
    case "$target" in
      /var/www/local-plugins-live/*)
        if [ ! -e "$target" ]; then
          echo "Removing stale local plugin symlink: $(basename "$existing")"
          rm -f "$existing"
        fi
        ;;
    esac
  done

  for plugin_dir in "$live_root"/*; do
    if [ ! -d "$plugin_dir" ]; then
      continue
    fi

    plugin=$(basename "$plugin_dir")
    plugin_link="$plugins_root/$plugin"

    if [ "$plugin" = ".gitkeep" ]; then
      continue
    fi

    if [ -e "$plugin_link" ] && [ ! -L "$plugin_link" ]; then
      continue
    fi

    target=$(readlink "$plugin_link" 2>/dev/null || true)
    if [ "$target" = "$plugin_dir" ]; then
      continue
    fi

    rm -f "$plugin_link"
    ln -s "$plugin_dir" "$plugin_link"
    echo "Linked local plugin: $plugin"
  done
}

if [ "${1:-}" = "--watch" ]; then
  while true; do
    sync_once
    sleep 2
  done
else
  sync_once
fi
