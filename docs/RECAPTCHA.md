# reCAPTCHA on mail-sending endpoints

## Why

The VPS provider blocked the outbound SMTP port after bots hammered the public
auth endpoints and drove a spike in outbound OTP / password-reset mail. Every
public endpoint that sends mail (or is a spam vector) now requires a Google
**reCAPTCHA v2 Invisible** token that the backend verifies **before** any mail is
sent. No valid token → `422` and the request never reaches the mail transport.

Gated endpoints (`routes/api.php`):

| Endpoint | Sends mail? |
|---|---|
| `POST /api/register` | yes (OTP) |
| `POST /api/resend-otp` | yes (OTP) |
| `POST /api/forgot-password` | yes (reset code) — also used by the reset-OTP "resend" button |
| `POST /api/login` | no, but credential-stuffing vector |
| `POST /api/contact/submit` | no today (mail notification is planned) |

Each also has a `throttle` limit as defence-in-depth against volume.

## Creating the keys

1. Open <https://www.google.com/recaptcha/admin/create>
2. **Label:** `Ikigai Connect`
3. **Type:** reCAPTCHA v2 → **"Invisible reCAPTCHA badge"**
4. **Domains:** add every domain the app / site will report as its origin, e.g.
   - `ikigaicompanyjpn.com`   ← current key is registered here; must match
     `RECAPTCHA_BASE_URL` in `mcq-app/config/constants.ts`
   - `localhost` (for local app development)

   The mobile WebView reports `RECAPTCHA_BASE_URL` as its origin — that domain
   MUST be in this list or Google returns "Invalid domain for site key" and the
   app shows "Could not verify you are human" on every attempt. Quick check:
   `curl -s "https://www.google.com/recaptcha/api2/anchor?k=<SITEKEY>&co=$(printf https://<domain>:443 | base64 | tr '+/' '-_' | tr -d =)&size=invisible&cb=x" | grep -o "Invalid domain for site key"`
   — no output means the domain is accepted.
5. Accept the terms and submit. You get two keys:
   - **Site key** → mobile app (`mcq-app/.env` → `EXPO_PUBLIC_RECAPTCHA_SITE_KEY`)
   - **Secret key** → backend (`mcq-backend/.env` → `RECAPTCHA_SECRET`)

## Backend configuration (`mcq-backend/.env`)

```
RECAPTCHA_ENABLED=true
RECAPTCHA_SECRET=<secret key from Google>
```

- `RECAPTCHA_ENABLED=false` bypasses verification entirely — use it locally and in
  CI. The PHPUnit suite sets this in `phpunit.xml`.
- After editing `.env` on the server run `php artisan config:clear`.

Config lives in `config/services.php` (`recaptcha`), verification in
`app/Http/Middleware/VerifyRecaptcha.php` (alias `recaptcha` in `bootstrap/app.php`).

Behaviour: missing token → 422; Google says invalid → 422; Google unreachable →
422 (fail closed — we would rather block than let unverified traffic through).
All rejections are logged via `Log::warning` with the client IP and route.

## Mobile app

The app obtains a token with an invisible widget rendered in a WebView
(`components/Recaptcha.tsx`, `useRecaptcha()` hook) and sends it as
`recaptcha_token` in the request body. Site key + allowed base URL are in
`config/constants.ts`. Because `react-native-webview` is a native module, a new
dev-client / EAS build is required after pulling this change.

## Testing without the real Google service

- Backend: `php artisan test --filter=RecaptchaTest` (mocks the verify call).
- Manual: with `RECAPTCHA_ENABLED=true`,
  `curl -X POST https://app.ikigaijobplacement.com/api/forgot-password -H 'Content-Type: application/json' -d '{"email":"x@y.com"}'`
  → `422 {"success":false,"message":"reCAPTCHA verification required."}`.
