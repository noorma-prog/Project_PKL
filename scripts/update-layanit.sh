#!/usr/bin/env bash
set -euo pipefail

APP_DIR=/home/magang/layanit
NODE_BIN=/home/magang/.local/node-v22.23.4-linux-x64/bin

if [[ ${EUID} -eq 0 ]]; then
    printf 'Run this command as user magang, not root.\n' >&2
    exit 1
fi

if [[ "$(id -un)" != magang ]]; then
    printf 'Run this command as user magang.\n' >&2
    exit 1
fi

if [[ ! -d "$APP_DIR/.git" || -L "$APP_DIR/.git" ]]; then
    printf 'Git checkout not found at %s.\n' "$APP_DIR" >&2
    exit 1
fi

if [[ "$(git -C "$APP_DIR" branch --show-current)" != main ]]; then
    printf 'The hosted checkout is not on branch main; refusing to deploy.\n' >&2
    exit 1
fi

if ! git -C "$APP_DIR" diff --quiet || ! git -C "$APP_DIR" diff --cached --quiet; then
    printf 'Hosted tracked files have local edits. Commit or resolve them before updating.\n' >&2
    exit 1
fi

if [[ ! -x "$NODE_BIN/npm" || ! -x "$NODE_BIN/node" ]]; then
    printf 'Expected Node.js 22 toolchain is missing at %s.\n' "$NODE_BIN" >&2
    exit 1
fi

export PATH="$NODE_BIN:$PATH"
node -e 'const [major, minor] = process.versions.node.split(".").map(Number); process.exit(major > 22 || (major === 22 && minor >= 12) ? 0 : 1)' || {
    printf 'Node.js 22.12 or newer is required to build frontend assets.\n' >&2
    exit 1
}

cd "$APP_DIR"
git fetch --prune origin main

if ! git merge-base --is-ancestor HEAD origin/main; then
    printf 'Local main is ahead of or has diverged from origin/main; refusing to overwrite commits.\n' >&2
    exit 1
fi

git merge --ff-only origin/main
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm install --no-audit --no-fund --no-package-lock
npm run build
php artisan migrate --force
php artisan config:cache
php artisan view:cache

printf 'LayanIT updated successfully from origin/main.\n'
