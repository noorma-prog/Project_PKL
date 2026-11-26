<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Production email verification

Verification links are absolute signed URLs: their signature includes the URL scheme, host, path, expiry, and the application key. Production must use `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL=https://layanit.tesmagang.site`. Keep the existing `APP_KEY`; never regenerate it to fix a signature mismatch. The deployment script validates that the key exists but does not print or replace it.

The application pins generated production URLs to `APP_URL` and HTTPS. The included Nginx deployment also passes `HTTPS on` to PHP-FPM. If another reverse proxy terminates TLS, configure `TRUSTED_PROXIES` in `.env` with only that proxy's actual IP address(es), and have it forward the original protocol and host. Do not trust all proxy addresses.

After updating production settings or code, run as the application user:

```bash
cd /home/magang/layanit
php artisan config:clear
php artisan config:cache
```

Verification links are valid for 60 minutes (`auth.verification.expire`) and can be reissued from the verification page or the login page; resend requests are throttled. Verification is persisted on the user record and does not depend on the browser session that registered. A valid link clears stale login errors, returns the user to login with a success notice, and pre-fills only the verified email (never the password); invalid or expired links retain signed-URL validation and show a resend option. Login checks the stored password hash and reports a role mismatch separately; public registration can only create reporter accounts. Confirm delivery and the complete registration-to-verification flow with a mailbox you control after deployment.

## Google sign-in (local setup)

Google sign-in uses Laravel Socialite and only authenticates an existing LayanIT account whose email exactly matches a Google-verified email. The account role, active status, and staff approval status remain controlled by the LayanIT database. A verified Google email without an account may continue to the existing reporter registration form; no account is created until that form is submitted, and the role is always set to User/Pelapor. The normal LayanIT email-verification process still applies. Keep the existing `.env` private and never commit OAuth credentials.

1. In [Google Cloud Console](https://console.cloud.google.com/), create or select a project and configure the OAuth consent screen (Google Auth Platform). Select the appropriate audience; while the app is in testing, add the Google accounts that may sign in under **Test users**.
2. Create an OAuth client ID with application type **Web application**. Add the origin used by the local app (for example `http://localhost:8000`) under **Authorized JavaScript origins** if the console requests it. This server-side OAuth flow does not otherwise require JavaScript origins.
3. Add the exact callback URI `http://localhost:8000/auth/google/callback` under **Authorized redirect URIs** for local testing. For later hosting configuration, add `https://layanit.tesmagang.site/auth/google/callback` as a separate URI. Google requires an exact match including scheme, host, port, and path.
4. Put the issued values only in the local `.env` file:

   ```dotenv
   GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```

   Start the local application with `APP_URL=http://localhost:8000`, then run `php artisan config:clear` after changing the values. Never paste the real client secret into source files, screenshots, or Git.

Production is not configured or deployed by this change. After approval, set the three Google OAuth environment values on the host (with `GOOGLE_REDIRECT_URI=https://layanit.tesmagang.site/auth/google/callback`), make sure HTTPS session cookies and the existing proxy/URL settings are correct, and clear/cache Laravel config as part of the approved deployment procedure.

## Ticket categories and activity schedules

Reporters enter a free-text issue category; the submitted category is stored as a category record and linked to the ticket. Admin category CRUD routes are disabled while existing category/ticket records remain intact. Each ticket also stores the requested activity date and start/end times separately from its automatically generated submission timestamps. Old tickets keep nullable schedule fields.

Ticket lists and the admin recap can be sorted independently by nearest activity or newest submission, with stable database ordering for equal submission times. App and MySQL/MariaDB connection defaults use `Asia/Makassar` / `+08:00`; deployment applies the additive ticket schedule migration with `php artisan migrate --force`.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
