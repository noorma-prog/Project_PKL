#!/usr/bin/env bash
set -euo pipefail
PATH=/usr/sbin:/usr/bin:/sbin:/bin
export PATH

APP_DIR=/home/magang/layanit
SITE=/etc/nginx/sites-available/layanit
LINK=/etc/nginx/sites-enabled/layanit

if [[ ${EUID} -ne 0 ]]; then
    printf 'Run this helper as root.\n' >&2
    exit 1
fi

if [[ $# -ne 0 ]]; then
    printf 'This helper does not accept arguments.\n' >&2
    exit 2
fi

for path in \
    "$APP_DIR" \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/database"
do
    if [[ ! -d "$path" || -L "$path" ]]; then
        printf 'Expected application directory is missing or is a symlink: %s\n' "$path" >&2
        exit 1
    fi
done

for path in "$APP_DIR/.env" "$APP_DIR/database/database.sqlite"; do
    if [[ ! -f "$path" || -L "$path" ]]; then
        printf 'Expected application file is missing or is a symlink: %s\n' "$path" >&2
        exit 1
    fi
done

if [[ ! -S /run/php/php8.4-fpm.sock ]]; then
    printf 'PHP-FPM socket is missing: /run/php/php8.4-fpm.sock\n' >&2
    exit 1
fi

sed -i 's|^APP_URL=.*|APP_URL=https://layanit.tesmagang.site|' "$APP_DIR/.env"
chmod 711 /home/magang
chgrp -R www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 2775 {} +
chgrp www-data "$APP_DIR/.env" "$APP_DIR/database" "$APP_DIR/database/database.sqlite"
chmod 640 "$APP_DIR/.env"
chmod 2770 "$APP_DIR/database"
chmod 660 "$APP_DIR/database/database.sqlite"

runuser -u magang -- bash -c 'cd /home/magang/layanit && php artisan config:cache --no-ansi'

tmp_site=$(mktemp /etc/nginx/sites-available/.layanit.XXXXXX)
trap 'rm -f "$tmp_site"' EXIT
cat > "$tmp_site" <<'NGINX'
server {
    listen 80;
    server_name layanit.tesmagang.site;

    root /home/magang/layanit/public;
    index index.php;
    charset utf-8;
    client_max_body_size 10m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param HTTPS on;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
chown root:root "$tmp_site"
chmod 644 "$tmp_site"
mv -f "$tmp_site" "$SITE"
trap - EXIT

ln -sfn "$SITE" "$LINK"
nginx -t
systemctl reload nginx
curl --fail --silent --show-error --output /dev/null --max-time 10 \
    -H 'Host: layanit.tesmagang.site' \
    http://127.0.0.1/login
printf 'LayanIT Nginx site installed and reloaded.\n'
