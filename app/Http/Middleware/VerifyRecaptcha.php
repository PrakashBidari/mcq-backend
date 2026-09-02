<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies a Google reCAPTCHA v2 (Invisible) token before letting the request
 * reach controllers that trigger outbound SMTP mail (register / resend OTP /
 * forgot password) or that are otherwise public spam vectors (login / contact).
 *
 * Bots that cannot solve the challenge are rejected here and never reach the
 * mail transport, which is what got our SMTP port blocked in the first place.
 *
 * Toggle with RECAPTCHA_ENABLED=false for local development and tests.
 */
class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('services.recaptcha.enabled')) {
            return $next($request);
        }

        $token = $request->input('recaptcha_token') ?: $request->header('X-Recaptcha-Token');

        if (!$token) {
            return $this->fail($request, 'reCAPTCHA verification required.', ['missing-token']);
        }

        try {
            $response = Http::asForm()->timeout(10)->post(config('services.recaptcha.verify_url'), [
                'secret' => config('services.recaptcha.secret'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $passed = $response->successful() && $response->json('success') === true;

            if (!$passed) {
                return $this->fail($request, 'reCAPTCHA verification failed. Please try again.', $response->json('error-codes', []));
            }
        } catch (\Throwable $e) {
            // Fail closed: if we cannot reach Google we would rather block the
            // request than let unverified traffic hit the mail server.
            Log::warning('reCAPTCHA verification errored', [
                'ip' => $request->ip(),
                'route' => $request->path(),
                'error' => $e->getMessage(),
            ]);

            return $this->fail($request, 'reCAPTCHA verification failed. Please try again.', ['verify-request-failed']);
        }

        return $next($request);
    }

    private function fail(Request $request, string $message, array $errorCodes = []): Response
    {
        Log::warning('reCAPTCHA verification rejected', [
            'ip' => $request->ip(),
            'route' => $request->path(),
            'error_codes' => $errorCodes,
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
        ], 422);
    }
}
