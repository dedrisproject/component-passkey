<?php
/**
 * JSON API for the passkey demo.
 *
 * Actions (POST, JSON body):
 *  - register-options : options for navigator.credentials.create()
 *  - register-verify  : verify and store a new passkey
 *  - login-options    : options for navigator.credentials.get()
 *  - login-verify     : verify an assertion and open the session
 *  - form-login       : classic username/password login (demo)
 *  - logout           : close the session
 *  - delete-passkey   : remove a stored passkey (demo management)
 *
 * Every response includes a "debug" array describing what the server did,
 * which the frontend prints in the debug console.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$debug = [];
$dbg = function (string $message) use (&$debug): void {
    $debug[] = $message;
};

$respond = function (array $payload, int $status = 200) use (&$debug): never {
    http_response_code($status);
    echo json_encode($payload + ['debug' => $debug], JSON_UNESCAPED_SLASHES);
    exit;
};

$fail = fn(string $error, int $status = 400) => $respond(['ok' => false, 'error' => $error], $status);

$store = new CredentialStore(CREDENTIAL_FILE);
$action = $_GET['action'] ?? '';
$input = json_decode((string)file_get_contents('php://input'), true) ?: [];

$rpId = WebAuthnHelper::currentRpId();
$origin = WebAuthnHelper::currentOrigin();

try {
    switch ($action) {

        case 'register-options': {
            $user = currentUser() ?? $fail('Not logged in', 401);
            $challenge = WebAuthnHelper::createChallenge();
            $_SESSION['register_challenge'] = $challenge;

            // Stable user handle so the authenticator can overwrite instead of duplicating.
            $userHandle = WebAuthnHelper::base64urlEncode(hash('sha256', 'user:' . $user, true));

            // Tell the authenticator which credentials already exist, to avoid duplicates.
            $excludeCredentials = array_map(
                fn($id) => ['type' => 'public-key', 'id' => $id],
                array_keys($store->forUser($user))
            );

            $dbg("→ register-options: user='{$user}', rpId='{$rpId}'");
            $dbg('  challenge: ' . $challenge);
            $dbg('  excludeCredentials: ' . count($excludeCredentials));

            $respond(['ok' => true, 'publicKey' => [
                'challenge' => $challenge,
                'rp' => ['id' => $rpId, 'name' => APP_NAME],
                'user' => [
                    'id' => $userHandle,
                    'name' => $user,
                    'displayName' => $user,
                ],
                'pubKeyCredParams' => [
                    ['type' => 'public-key', 'alg' => WebAuthnHelper::ALG_ES256],
                    ['type' => 'public-key', 'alg' => WebAuthnHelper::ALG_RS256],
                ],
                'authenticatorSelection' => [
                    'residentKey' => 'preferred',
                    'userVerification' => 'preferred',
                ],
                'excludeCredentials' => array_values($excludeCredentials),
                'attestation' => 'none',
                'timeout' => 60000,
            ]]);
        }

        case 'register-verify': {
            $user = currentUser() ?? $fail('Not logged in', 401);
            $challenge = $_SESSION['register_challenge'] ?? $fail('No pending registration challenge');
            unset($_SESSION['register_challenge']); // single use

            $clientDataJson = WebAuthnHelper::base64urlDecode((string)($input['clientDataJSON'] ?? ''));
            $attestationObject = WebAuthnHelper::base64urlDecode((string)($input['attestationObject'] ?? ''));

            $dbg("→ register-verify: user='{$user}'");
            WebAuthnHelper::checkClientData($clientDataJson, 'webauthn.create', $challenge, $origin);
            $dbg("  ✔ clientDataJSON ok (type=webauthn.create, challenge match, origin={$origin})");

            $cred = WebAuthnHelper::parseAttestation($attestationObject, $rpId);
            $credentialId = WebAuthnHelper::base64urlEncode($cred['credentialId']);
            $dbg('  ✔ attestationObject parsed: credentialId=' . substr($credentialId, 0, 16) . '…, alg=' . $cred['alg']);
            $dbg('  ✔ public key extracted (PEM, ' . strlen($cred['publicKeyPem']) . ' bytes)');

            $store->save($credentialId, [
                'username'     => $user,
                'publicKeyPem' => $cred['publicKeyPem'],
                'signCount'    => $cred['signCount'],
                'alg'          => $cred['alg'],
                'aaguid'       => $cred['aaguid'],
                'label'        => (string)($input['label'] ?? ''),
                'createdAt'    => date('c'),
            ]);
            $dbg('  ✔ credential stored in ' . basename(CREDENTIAL_FILE));

            $respond(['ok' => true, 'credentialId' => $credentialId]);
        }

        case 'login-options': {
            $challenge = WebAuthnHelper::createChallenge();
            $_SESSION['login_challenge'] = $challenge;

            $dbg("→ login-options: rpId='{$rpId}'");
            $dbg('  challenge: ' . $challenge);
            $dbg('  allowCredentials empty → discoverable credential (the OS picks the passkey)');

            $respond(['ok' => true, 'publicKey' => [
                'challenge' => $challenge,
                'rpId' => $rpId,
                'allowCredentials' => [],
                'userVerification' => 'preferred',
                'timeout' => 60000,
            ]]);
        }

        case 'login-verify': {
            $challenge = $_SESSION['login_challenge'] ?? $fail('No pending login challenge');
            unset($_SESSION['login_challenge']); // single use

            $credentialId = (string)($input['credentialId'] ?? '');
            $clientDataJson = WebAuthnHelper::base64urlDecode((string)($input['clientDataJSON'] ?? ''));
            $authenticatorData = WebAuthnHelper::base64urlDecode((string)($input['authenticatorData'] ?? ''));
            $signature = WebAuthnHelper::base64urlDecode((string)($input['signature'] ?? ''));

            $dbg('→ login-verify: credentialId=' . substr($credentialId, 0, 16) . '…');

            $cred = $store->get($credentialId) ?? $fail('Unknown credential — was it registered on this host?', 404);
            $dbg("  ✔ credential found, belongs to user='{$cred['username']}'");

            WebAuthnHelper::checkClientData($clientDataJson, 'webauthn.get', $challenge, $origin);
            $dbg("  ✔ clientDataJSON ok (type=webauthn.get, challenge match, origin={$origin})");

            $auth = WebAuthnHelper::parseAuthenticatorData($authenticatorData, $rpId);
            $dbg('  ✔ authenticatorData ok (rpIdHash match, userPresent, userVerified=' . ($auth['userVerified'] ? 'yes' : 'no') . ')');

            if (!WebAuthnHelper::verifyAssertion($authenticatorData, $clientDataJson, $signature, $cred['publicKeyPem'])) {
                $fail('Invalid signature', 401);
            }
            $dbg('  ✔ signature verified with the stored public key');

            // Sign counter check (0 means the authenticator does not use counters, e.g. iCloud Keychain).
            if ($auth['signCount'] > 0 && $auth['signCount'] <= ($cred['signCount'] ?? 0)) {
                $fail('Sign counter did not increase — possible cloned authenticator', 401);
            }
            $cred['signCount'] = $auth['signCount'];
            $cred['lastUsedAt'] = date('c');
            $store->save($credentialId, $cred);

            session_regenerate_id(true);
            $_SESSION['user'] = $cred['username'];
            $_SESSION['login_method'] = 'passkey';
            $dbg("  ✔ session opened for '{$cred['username']}' (login_method=passkey)");

            $respond(['ok' => true, 'user' => $cred['username']]);
        }

        case 'form-login': {
            $username = (string)($input['username'] ?? '');
            $password = (string)($input['password'] ?? '');
            $dbg("→ form-login: username='{$username}'");

            if ($username !== DEMO_USERNAME || !hash_equals(DEMO_PASSWORD, $password)) {
                $dbg('  ✘ invalid credentials');
                $fail('Invalid credentials', 401);
            }
            session_regenerate_id(true);
            $_SESSION['user'] = $username;
            $_SESSION['login_method'] = 'form';
            $dbg("  ✔ session opened for '{$username}' (login_method=form)");

            $respond(['ok' => true, 'user' => $username]);
        }

        case 'logout': {
            $user = currentUser();
            unset($_SESSION['user'], $_SESSION['login_method']);
            session_regenerate_id(true);
            $dbg('→ logout: session closed' . ($user ? " for '{$user}'" : ''));
            $respond(['ok' => true]);
        }

        case 'delete-passkey': {
            currentUser() ?? $fail('Not logged in', 401);
            $credentialId = (string)($input['credentialId'] ?? '');
            $cred = $store->get($credentialId);
            if ($cred === null || $cred['username'] !== currentUser()) {
                $fail('Credential not found', 404);
            }
            $store->delete($credentialId);
            $dbg('→ delete-passkey: removed ' . substr($credentialId, 0, 16) . '…');
            $respond(['ok' => true]);
        }

        default:
            $fail('Unknown action', 404);
    }
} catch (WebAuthnException $e) {
    $dbg('  ✘ ' . $e->getMessage());
    $respond(['ok' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    $dbg('  ✘ internal error: ' . $e->getMessage());
    $respond(['ok' => false, 'error' => 'Internal error'], 500);
}
