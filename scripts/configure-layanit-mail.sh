#!/usr/bin/env bash
set -euo pipefail

if [[ ${EUID} -eq 0 ]]; then
    exec runuser -u magang -- "$0"
fi

APP_DIR=/home/magang/layanit
ENV_FILE="$APP_DIR/.env"

if [[ ! -f "$ENV_FILE" || -L "$ENV_FILE" || ! -w "$ENV_FILE" ]]; then
    printf 'Cannot safely update %s as user %s.\n' "$ENV_FILE" "$(id -un)" >&2
    exit 1
fi

read -r -p 'Gmail address: ' mail_username
if [[ ! "$mail_username" =~ ^[^[:space:]@]+@gmail\.com$ ]]; then
    printf 'Enter a valid @gmail.com address.\n' >&2
    exit 1
fi

read -r -s -p 'New Google App Password (input is hidden): ' mail_password
printf '\n'
mail_password=${mail_password// /}
if [[ ! "$mail_password" =~ ^[A-Za-z0-9]{16}$ ]]; then
    unset mail_password
    printf 'The App Password should contain 16 letters/digits; spaces are optional.\n' >&2
    exit 1
fi

export LAYANIT_MAIL_USERNAME="$mail_username"
export LAYANIT_MAIL_PASSWORD="$mail_password"
unset mail_username mail_password

php <<'PHP'
<?php
$path = '/home/magang/layanit/.env';
$username = getenv('LAYANIT_MAIL_USERNAME');
$password = getenv('LAYANIT_MAIL_PASSWORD');

if (!is_string($username) || !is_string($password) || is_link($path)) {
    fwrite(STDERR, "Mail configuration input or environment file is invalid.\n");
    exit(1);
}

$contents = file_get_contents($path);
if ($contents === false) {
    fwrite(STDERR, "Could not read the hosting environment file.\n");
    exit(1);
}

$settings = [
    'MAIL_MAILER' => 'smtp',
    'MAIL_SCHEME' => 'smtp',
    'MAIL_HOST' => 'smtp.gmail.com',
    'MAIL_PORT' => '587',
    'MAIL_USERNAME' => '"' . $username . '"',
    'MAIL_PASSWORD' => '"' . $password . '"',
    'MAIL_FROM_ADDRESS' => '"' . $username . '"',
    'MAIL_FROM_NAME' => '"LayanIT"',
];

foreach ($settings as $key => $value) {
    $line = $key . '=' . $value;
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
    if (preg_match($pattern, $contents) === 1) {
        $contents = preg_replace($pattern, $line, $contents, 1);
    } else {
        $contents = rtrim($contents, "\r\n") . PHP_EOL . $line . PHP_EOL;
    }
}

$temporary = tempnam(dirname($path), '.env-mail-');
if ($temporary === false) {
    fwrite(STDERR, "Could not prepare the environment file update.\n");
    exit(1);
}

$permissions = fileperms($path);
$written = file_put_contents($temporary, $contents, LOCK_EX);
if ($written === false
    || ($permissions !== false && !chmod($temporary, $permissions & 0777))
    || !rename($temporary, $path)) {
    @unlink($temporary);
    fwrite(STDERR, "Could not safely update the environment file.\n");
    exit(1);
}
PHP

unset LAYANIT_MAIL_USERNAME LAYANIT_MAIL_PASSWORD
cd "$APP_DIR"
php artisan config:clear --no-ansi
php artisan config:cache --no-ansi
printf 'Gmail SMTP settings saved and Laravel configuration refreshed.\n'
