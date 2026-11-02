<?php

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->redirectUsersTo(function (Request $request): string {
            return match ($request->user()?->role) {
                User::ROLE_ADMIN => route('admin.dashboard'),
                User::ROLE_PADAL => route('padal.dashboard'),
                User::ROLE_TEKNISI => route('teknisi.dashboard'),
                User::ROLE_USER => route('user.dashboard'),
                default => route('profile.show'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidSignatureException $exception, Request $request) {
            if (!$request->routeIs('verification.verify')) {
                return null;
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'error',
                'Tautan verifikasi tidak valid atau telah kedaluwarsa. Minta tautan baru melalui formulir di halaman ini.'
            );
        });
    })->create();
