<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyRecaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Exercises the reCAPTCHA middleware in isolation (no DB) plus one end-to-end
 * check that the route is actually wired to it. The project's migrations are
 * MySQL-only, so DB-backed feature tests can't run under SQLite.
 */
class RecaptchaTest extends TestCase
{
    private function handle(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return (new VerifyRecaptcha())->handle($request, fn () => response('OK', 200));
    }

    private function request(array $body = []): Request
    {
        return Request::create('/api/register', 'POST', $body);
    }

    public function test_passes_through_when_recaptcha_disabled(): void
    {
        config(['services.recaptcha.enabled' => false]);

        $response = $this->handle($this->request());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_rejects_when_token_missing(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret' => 'test-secret']);

        $response = $this->handle($this->request());

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('reCAPTCHA verification required', $response->getContent());
    }

    public function test_rejects_when_google_says_token_invalid(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret' => 'test-secret']);
        Http::fake([
            'www.google.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $response = $this->handle($this->request(['recaptcha_token' => 'bad-token']));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('reCAPTCHA verification failed', $response->getContent());
    }

    public function test_passes_when_google_accepts_token(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret' => 'test-secret']);
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
        ]);

        $response = $this->handle($this->request(['recaptcha_token' => 'good-token']));

        $this->assertSame(200, $response->getStatusCode());
        Http::assertSent(fn ($req) => $req['secret'] === 'test-secret' && $req['response'] === 'good-token');
    }

    public function test_fails_closed_when_google_unreachable(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret' => 'test-secret']);
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('network down');
        });

        $response = $this->handle($this->request(['recaptcha_token' => 'any-token']));

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_token_can_be_supplied_via_header(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret' => 'test-secret']);
        Http::fake(['www.google.com/*' => Http::response(['success' => true])]);

        $request = $this->request();
        $request->headers->set('X-Recaptcha-Token', 'header-token');

        $response = $this->handle($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_route_is_wired_to_middleware(): void
    {
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret' => 'test-secret']);

        // No token -> middleware rejects before the controller touches the DB.
        $response = $this->postJson('/api/forgot-password', ['email' => 'someone@example.com']);

        $response->assertStatus(422)->assertJson([
            'success' => false,
            'message' => 'reCAPTCHA verification required.',
        ]);
    }
}
