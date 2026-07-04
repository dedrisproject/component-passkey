<?php
/**
 * Demo configuration.
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/lib/WebAuthnHelper.php';
require_once __DIR__ . '/lib/CredentialStore.php';

const APP_NAME = 'Passkey Component Demo';

// Demo form-login credentials (change them, or plug in your own user backend).
const DEMO_USERNAME = 'demo';
const DEMO_PASSWORD = 'demo';

// Where registered passkeys are stored (JSON file for the demo — use a DB in production).
const CREDENTIAL_FILE = __DIR__ . '/data/credentials.json';

// ---- language handling (it / en) ----

const SUPPORTED_LANGS = ['it', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['lang'])) {
    $browser = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'it', 0, 2);
    $_SESSION['lang'] = in_array($browser, SUPPORTED_LANGS, true) ? $browser : 'en';
}

$LANG = $_SESSION['lang'];
$T = require __DIR__ . '/lang/' . $LANG . '.php';

/** Translate helper. */
function t(string $key, array $vars = []): string
{
    global $T;
    $s = $T[$key] ?? $key;
    foreach ($vars as $k => $v) {
        $s = str_replace('{' . $k . '}', (string)$v, $s);
    }
    return $s;
}

// ---- demo session: you are logged in by default on first visit ----

if (!isset($_SESSION['visited'])) {
    $_SESSION['visited'] = true;
    $_SESSION['user'] = DEMO_USERNAME;
    $_SESSION['login_method'] = 'default';
}

function currentUser(): ?string
{
    return $_SESSION['user'] ?? null;
}
