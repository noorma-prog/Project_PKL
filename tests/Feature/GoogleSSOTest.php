<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleSSOTest extends TestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $app['config']->set([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function test_login_page_has_a_google_sign_in_option(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('atau')
            ->assertSee('Masuk dengan Google')
            ->assertSee(route('auth.google.redirect'));
    }

    public function test_google_redirect_uses_the_configured_callback_and_session_state(): void
    {
        $response = $this->get(route('auth.google.redirect'));
        $response->assertRedirectContains('accounts.google.com');
        $response->assertSessionHas('state');

        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('http://localhost/auth/google/callback', $query['redirect_uri']);
        $this->assertSame(session('state'), $query['state']);
    }

    public function test_google_callback_regenerates_the_authenticated_session(): void
    {
        $user = $this->createUser(User::ROLE_USER);
        $this->get(route('auth.google.redirect'));
        $sessionId = session()->getId();

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
            'email' => $user->email,
            'email_verified' => true,
        ]));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback', ['state' => session('state')]))
            ->assertRedirect(route('user.dashboard'));

        $this->assertNotSame($sessionId, session()->getId());
        $this->assertAuthenticatedAs($user);
    }

    public function test_verified_google_accounts_login_with_database_roles_and_redirects(): void
    {
        $destinations = [
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_PADAL => 'padal.dashboard',
            User::ROLE_TEKNISI => 'teknisi.dashboard',
            User::ROLE_USER => 'user.dashboard',
        ];

        foreach ($destinations as $role => $destination) {
            $user = $this->createUser($role);
            $provider = Mockery::mock(GoogleProvider::class);
            $provider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
                'email' => $user->email,
                'email_verified' => true,
            ]));
            Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

            $this->get(route('auth.google.callback', ['state' => 'callback-state', 'role' => User::ROLE_ADMIN]))
                ->assertRedirect(route($destination));

            $this->assertAuthenticatedAs($user);
            $this->assertSame($role, $user->fresh()->role);
            $this->post(route('logout'));
        }
    }

    public function test_unverified_google_email_is_denied_and_unknown_email_uses_reporter_registration(): void
    {
        $registered = $this->createUser(User::ROLE_USER);
        $initialCount = User::count();

        $unverifiedProvider = Mockery::mock(GoogleProvider::class);
        $unverifiedProvider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
            'email' => $registered->email,
            'email_verified' => false,
        ]));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($unverifiedProvider);

        $this->get(route('auth.google.callback', ['state' => 'callback-state']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun Google ini belum terdaftar atau belum memiliki akses ke LayanIT.');
        $this->assertGuest();

        $this->assertSame($initialCount, User::count());

        $googleEmail = 'new-reporter@example.test';
        $registrationProvider = Mockery::mock(GoogleProvider::class);
        $registrationProvider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
            'email' => $googleEmail,
            'name' => 'New Reporter',
            'email_verified' => true,
        ]));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($registrationProvider);

        $this->get(route('auth.google.callback', ['state' => 'callback-state']))
            ->assertRedirect(route('register'))
            ->assertSessionHas('google_registration_notice');
        $this->assertSame($initialCount, User::count());

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('New Reporter')
            ->assertSee('value="'.$googleEmail.'"', false);

        $registrationDetails = [
            'name' => 'New Reporter',
            'nrp_nip' => 'NRP-123',
            'phone_number' => '08123456789',
            'satuan_kerja' => 'SPKT',
            'email' => 'forged@example.test',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123',
            'role' => User::ROLE_ADMIN,
        ];

        $this->from(route('register'))
            ->post(route('register.post'), $registrationDetails)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'role']);
        $this->assertGuest();
        $this->assertSame($initialCount, User::count());

        Notification::fake();
        $registrationDetails['email'] = $googleEmail;
        unset($registrationDetails['role']);

        $this->post(route('register.post'), $registrationDetails)
            ->assertRedirect(route('verification.notice'));

        $newUser = User::where('email', $googleEmail)->firstOrFail();
        $this->assertAuthenticatedAs($newUser);
        $this->assertSame(User::ROLE_USER, $newUser->role);
        $this->assertTrue($newUser->requiresEmailVerification());
        $this->assertNull($newUser->email_verified_at);
        $this->assertSame($initialCount + 1, User::count());
    }

    public function test_callback_rejects_missing_or_invalid_oauth_state(): void
    {
        $response = $this->get(route('auth.google.callback', ['state' => 'forged-state']));

        $response->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Sesi login Google tidak valid atau telah kedaluwarsa. Silakan coba kembali.');
        $this->assertGuest();
    }

    public function test_google_login_requires_local_email_verification_when_the_account_requires_it(): void
    {
        $user = $this->createUser(User::ROLE_USER, [
            'requires_email_verification' => true,
            'email_verified_at' => null,
        ]);
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
            'email' => $user->email,
            'email_verified' => true,
        ]));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback', ['state' => 'callback-state']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun LayanIT Anda belum menyelesaikan verifikasi email.');

        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_google_login_stays_unavailable_until_oauth_credentials_are_configured(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Login Google belum dikonfigurasi. Hubungi Administrator.');
    }

    public function test_google_login_rejects_inactive_and_unapproved_staff_accounts(): void
    {
        $cases = [
            [$this->createUser(User::ROLE_USER, ['is_active' => false]), 'Akun Google ini belum terdaftar atau belum memiliki akses ke LayanIT.'],
            [$this->createUser(User::ROLE_PADAL, ['approval_status' => User::APPROVAL_PENDING]), 'Pendaftaran akun Anda masih menunggu persetujuan Administrator.'],
        ];

        foreach ($cases as [$user, $message]) {
            $provider = Mockery::mock(GoogleProvider::class);
            $provider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
                'email' => $user->email,
                'email_verified' => true,
            ]));
            Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

            $this->get(route('auth.google.callback', ['state' => 'callback-state']))
                ->assertRedirect(route('login'))
                ->assertSessionHas('error', $message);
            $this->assertGuest();
        }
    }

    private function createUser(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => ucfirst($role).' Test',
            'email' => $role.'-'.uniqid().'@example.test',
            'role' => $role,
            'is_active' => true,
            'approval_status' => User::APPROVAL_ACTIVE,
            'requires_email_verification' => false,
            'email_verified_at' => now(),
        ], $attributes));
    }
}
