# 🔐 Passkey Component

> 🇮🇹 [Leggi in italiano](README.it.md)

An open source, **dependency-free PHP component** to add **passkey (WebAuthn) login** to any website — plus a live demo page with a debug console that shows exactly what happens under the hood.

## Demo overview

The demo page ([index.php](index.php)) has three sections:

1. **Your session** — you are **logged in by default** as the demo user. From here you can press **Create Passkey** to register a passkey for future logins, see/delete your registered passkeys, and **Logout**.
2. **Sign in** — after logout you get a classic **username/password form** (demo: `demo` / `demo`) and a **Sign in with passkey** button.
3. **Download the snippet** — one click downloads a zip with everything you need to integrate passkeys into your own PHP site (library, API, JS, guide).

On the right, a **debug console** traces every step, both client-side (browser/WebAuthn events) and server-side (challenge generation, clientData checks, signature verification…). The UI is bilingual (🇮🇹/🇬🇧) with a one-click language switch.

## Quick start

Requirements: PHP 8.0+ with `ext-openssl` (both standard).

```bash
git clone https://github.com/dedrisproject/component-passkey.git
cd component-passkey
php -S localhost:8000
```

Open <http://localhost:8000> — done. WebAuthn requires HTTPS in production, but `http://localhost` is allowed by browsers for development.

## How it works

```
Browser                                 PHP server
───────                                 ──────────
Create Passkey ──────────────────────▶  api.php?action=register-options
                                        generates a random challenge (in session)
navigator.credentials.create() ◀──────  returns rp, user, challenge
(OS dialog: Touch ID / Windows Hello)
attestationObject ───────────────────▶  api.php?action=register-verify
                                        checks challenge + origin + rpId,
                                        extracts the COSE public key → PEM,
                                        stores it (data/credentials.json)

Sign in with passkey ────────────────▶  api.php?action=login-options
navigator.credentials.get() ◀─────────  returns a fresh challenge
signature ───────────────────────────▶  api.php?action=login-verify
                                        verifies the signature with the stored
                                        public key → opens the session
```

Everything is implemented in [lib/WebAuthnHelper.php](lib/WebAuthnHelper.php) (~350 lines): minimal CBOR decoder, COSE→PEM key conversion (ES256 + RS256), clientData/authenticatorData validation and signature verification via OpenSSL. **No Composer packages needed.**

## Integrate it into your site

Press **Download snippet** in the demo (or read [INTEGRATION.md](INTEGRATION.md)) — it explains how to:

- hook the API endpoints to *your* session and user backend,
- replace the JSON-file storage with a database table,
- wire the two buttons (`Create passkey`, `Sign in with passkey`) into your pages.

## Passkeys in a nutshell

- **You must already be logged in to create one.** A passkey attaches to an existing account: the user signs in (or signs up) the classic way, then adds a passkey from their profile. That is why the demo starts with you already logged in.
- **The private key lives on your device** — in the notebook/smartphone secure chip or in your password manager (iCloud Keychain, Google Password Manager, 1Password…). It never leaves it: the server stores only the public key, so there is nothing to steal and phishing does not work (the key only signs for its own domain).
- **Other devices:** if the passkey is cloud-synced you will find it on your other devices in the same ecosystem. If it is stored only on your notebook, your smartphone cannot use it — sign in with the password and register a second passkey from the phone (one account, many passkeys), or use the browser's QR-code "use another device" flow.
- **Passkey-only accounts:** once at least one passkey is registered you can disable username/password login for that account entirely — in your integration it is just a flag that hides the classic form. Make sure the user has a second passkey or a recovery path first.

## Security notes

- Challenges are random, single-use and validated server-side.
- Origin and `rpId` hash are checked on every operation.
- Attestation is `none` (the passkey default): only the public key is stored.
- Sign counter is enforced when the authenticator provides one.
- The demo stores credentials in `data/credentials.json` — fine for trying it out, use a DB in production.

## Troubleshooting

- **Passkey created but not listed after reload** → the `data/` directory is not writable by the web server. Fix: `chmod 775 data` (or `chown www-data data` depending on your setup). The debug console shows the exact error before the OS dialog even opens.
- **The OS dialog never opens** → WebAuthn requires HTTPS (or `http://localhost` in development) and a browser with passkey support.
- The debug console persists across page reloads (sessionStorage) — check it first, every client and server step is traced there.

## Project structure

```
index.php              demo page (3 sections + debug console)
api.php                JSON API (register/login/logout)
download.php           builds the integration snippet zip
config.php             demo bootstrap: session, demo user, language
lib/WebAuthnHelper.php WebAuthn core (CBOR, COSE→PEM, verify)
lib/CredentialStore.php JSON-file credential storage
assets/app.js          client logic + debug console
lang/it.php, en.php    translations
INTEGRATION.md         integration guide (en/it)
```

## License

MIT — use it, break it, improve it. PRs welcome!
