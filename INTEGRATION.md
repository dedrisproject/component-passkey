# Passkey Snippet — Integration Guide / Guida all'integrazione

> 🇬🇧 English first, 🇮🇹 italiano più sotto.

---

## 🇬🇧 English

### What's in the zip

| File | Purpose |
|---|---|
| `lib/WebAuthnHelper.php` | Dependency-free WebAuthn library (CBOR parsing, COSE→PEM, signature verification) |
| `lib/CredentialStore.php` | Minimal JSON-file credential storage (replace with your DB) |
| `api.php` | JSON endpoints: register/login options and verification |
| `config.php` + `lang/` | Demo bootstrap (session, demo user, it/en strings) — replace with your own bootstrap |
| `assets/app.js` | Client JavaScript (calls the API and `navigator.credentials`) |

### Requirements

- PHP **8.0+** with `ext-openssl` (standard).
- **HTTPS** — WebAuthn only works on secure origins. `http://localhost` is the only exception, useful for development.
- A modern browser with a platform authenticator (Touch ID, Face ID, Windows Hello, Android) or a security key.

### Steps

1. **Copy the files** into your project keeping the paths (`lib/`, `api.php`, `assets/app.js`) or adjust the paths in `api.php` and in the `fetch()` calls of `app.js`.

2. **Hook up your session.** The snippet assumes a logged-in user is available. Replace the demo bits in `api.php`:
   - `currentUser()` → return the username of your logged-in user;
   - the `form-login` case → call your own password verification;
   - after `login-verify` succeeds → open *your* session the way your app does.

3. **Swap the storage.** `CredentialStore` writes a JSON file. In production store the same fields in a table:

   ```sql
   CREATE TABLE passkeys (
     credential_id VARCHAR(255) PRIMARY KEY, -- base64url
     username      VARCHAR(255) NOT NULL,
     public_key    TEXT NOT NULL,            -- PEM
     sign_count    INT NOT NULL DEFAULT 0,
     created_at    DATETIME NOT NULL
   );
   ```

4. **Add the buttons** to your pages and bind them like `assets/app.js` does:
   - *Create passkey* (user already logged in): `register-options` → `navigator.credentials.create()` → `register-verify`;
   - *Login with passkey*: `login-options` → `navigator.credentials.get()` → `login-verify`.

5. **Test**: register a passkey while logged in, log out, then sign back in with the passkey.

### Security notes

- Challenges are single-use and stored server-side in the session — never trust the client copy.
- The origin and the `rpId` hash are verified on every operation; a passkey registered on `example.com` cannot be replayed elsewhere.
- Attestation is `none` (the passkey standard default): the server does not learn anything about the device model, it only stores the public key.
- The sign counter is checked when the authenticator provides one (many cloud-synced passkeys always report 0 — that is normal and accepted).

---

## 🇮🇹 Italiano

### Cosa contiene lo zip

| File | Scopo |
|---|---|
| `lib/WebAuthnHelper.php` | Libreria WebAuthn senza dipendenze (parsing CBOR, COSE→PEM, verifica firma) |
| `lib/CredentialStore.php` | Storage minimale su file JSON (da sostituire con il tuo DB) |
| `api.php` | Endpoint JSON: opzioni e verifica per registrazione e login |
| `config.php` + `lang/` | Bootstrap demo (sessione, utente demo, stringhe it/en) — da sostituire con il tuo bootstrap |
| `assets/app.js` | JavaScript client (chiama le API e `navigator.credentials`) |

### Requisiti

- PHP **8.0+** con `ext-openssl` (standard).
- **HTTPS** — WebAuthn funziona solo su origin sicure. L'unica eccezione è `http://localhost`, utile in sviluppo.
- Un browser moderno con un authenticator di piattaforma (Touch ID, Face ID, Windows Hello, Android) o una chiave di sicurezza.

### Passaggi

1. **Copia i file** nel tuo progetto mantenendo i percorsi (`lib/`, `api.php`, `assets/app.js`) oppure adatta i percorsi in `api.php` e nelle chiamate `fetch()` di `app.js`.

2. **Collega la tua sessione.** Lo snippet presuppone un utente loggato. Sostituisci le parti demo in `api.php`:
   - `currentUser()` → restituisci lo username del tuo utente loggato;
   - il case `form-login` → richiama la tua verifica password;
   - dopo il successo di `login-verify` → apri la *tua* sessione come fa la tua app.

3. **Sostituisci lo storage.** `CredentialStore` scrive un file JSON. In produzione salva gli stessi campi in una tabella:

   ```sql
   CREATE TABLE passkeys (
     credential_id VARCHAR(255) PRIMARY KEY, -- base64url
     username      VARCHAR(255) NOT NULL,
     public_key    TEXT NOT NULL,            -- PEM
     sign_count    INT NOT NULL DEFAULT 0,
     created_at    DATETIME NOT NULL
   );
   ```

4. **Aggiungi i pulsanti** alle tue pagine e collegali come fa `assets/app.js`:
   - *Crea passkey* (utente già loggato): `register-options` → `navigator.credentials.create()` → `register-verify`;
   - *Accedi con passkey*: `login-options` → `navigator.credentials.get()` → `login-verify`.

5. **Prova**: registra una passkey da loggato, fai logout e rientra con la passkey.

### Note di sicurezza

- Le challenge sono monouso e conservate lato server nella sessione — non fidarti mai della copia lato client.
- L'origin e l'hash del `rpId` vengono verificati a ogni operazione; una passkey registrata su `example.com` non può essere riutilizzata altrove.
- L'attestation è `none` (il default dello standard passkey): il server non impara nulla sul modello del dispositivo, salva solo la chiave pubblica.
- Il sign counter viene controllato quando l'authenticator lo fornisce (molte passkey sincronizzate in cloud riportano sempre 0 — è normale e accettato).
