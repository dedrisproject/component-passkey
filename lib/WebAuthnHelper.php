<?php
/**
 * WebAuthnHelper — minimal, dependency-free WebAuthn (passkey) helper for PHP.
 *
 * Supports:
 *  - Registration (attestation "none"): parses attestationObject, extracts the
 *    credential id and COSE public key, converts it to a PEM key.
 *  - Authentication: verifies the assertion signature (ES256 and RS256).
 *
 * Requires: PHP >= 8.0, ext-openssl.
 *
 * This is intentionally small so it can be dropped into any project.
 * It does NOT validate attestation certificates (fine for "none" attestation,
 * which is what passkeys use in practice).
 */

class WebAuthnException extends RuntimeException {}

final class Cbor
{
    /** Wrapper to distinguish CBOR byte strings from text strings. */
    public static function decode(string $data): mixed
    {
        $offset = 0;
        $value = self::decodeItem($data, $offset);
        return $value;
    }

    /**
     * Decodes one CBOR item starting at $offset and advances $offset past it.
     * Byte strings are returned as CborBytes objects.
     */
    public static function decodeItem(string $d, int &$o): mixed
    {
        if ($o >= strlen($d)) {
            throw new WebAuthnException('CBOR: unexpected end of data');
        }
        $initial = ord($d[$o++]);
        $major = $initial >> 5;
        $info = $initial & 0x1f;

        if ($major === 7) {
            return match ($info) {
                20 => false,
                21 => true,
                22 => null,
                default => throw new WebAuthnException('CBOR: unsupported simple/float value'),
            };
        }

        $len = self::readLength($d, $o, $info);

        switch ($major) {
            case 0: // unsigned int
                return $len;
            case 1: // negative int
                return -1 - $len;
            case 2: // byte string
                $v = self::readBytes($d, $o, $len);
                return new CborBytes($v);
            case 3: // text string
                return self::readBytes($d, $o, $len);
            case 4: // array
                $arr = [];
                for ($i = 0; $i < $len; $i++) {
                    $arr[] = self::decodeItem($d, $o);
                }
                return $arr;
            case 5: // map
                $map = [];
                for ($i = 0; $i < $len; $i++) {
                    $k = self::decodeItem($d, $o);
                    if ($k instanceof CborBytes) {
                        $k = $k->value;
                    }
                    if (!is_int($k) && !is_string($k)) {
                        throw new WebAuthnException('CBOR: unsupported map key type');
                    }
                    $map[$k] = self::decodeItem($d, $o);
                }
                return $map;
            case 6: // tag — skip it, return the tagged value
                return self::decodeItem($d, $o);
        }
        throw new WebAuthnException('CBOR: invalid major type');
    }

    private static function readLength(string $d, int &$o, int $info): int
    {
        if ($info < 24) {
            return $info;
        }
        return match ($info) {
            24 => ord(self::readBytes($d, $o, 1)),
            25 => unpack('n', self::readBytes($d, $o, 2))[1],
            26 => unpack('N', self::readBytes($d, $o, 4))[1],
            27 => unpack('J', self::readBytes($d, $o, 8))[1],
            default => throw new WebAuthnException('CBOR: indefinite-length items are not supported'),
        };
    }

    private static function readBytes(string $d, int &$o, int $len): string
    {
        if ($o + $len > strlen($d)) {
            throw new WebAuthnException('CBOR: truncated data');
        }
        $v = substr($d, $o, $len);
        $o += $len;
        return $v;
    }
}

final class CborBytes
{
    public function __construct(public readonly string $value) {}
}

final class WebAuthnHelper
{
    public const ALG_ES256 = -7;
    public const ALG_RS256 = -257;

    public static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64urlDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new WebAuthnException('Invalid base64url data');
        }
        return $decoded;
    }

    /** Generates a cryptographically random challenge (base64url). */
    public static function createChallenge(int $bytes = 32): string
    {
        return self::base64urlEncode(random_bytes($bytes));
    }

    /**
     * Validates the clientDataJSON sent by the browser.
     *
     * @param string $clientDataJson   raw JSON string
     * @param string $expectedType     'webauthn.create' or 'webauthn.get'
     * @param string $expectedChallenge base64url challenge stored in session
     * @param string $expectedOrigin   e.g. 'https://example.com'
     */
    public static function checkClientData(string $clientDataJson, string $expectedType, string $expectedChallenge, string $expectedOrigin): array
    {
        $clientData = json_decode($clientDataJson, true);
        if (!is_array($clientData)) {
            throw new WebAuthnException('clientDataJSON is not valid JSON');
        }
        if (($clientData['type'] ?? '') !== $expectedType) {
            throw new WebAuthnException("Unexpected clientData type (expected {$expectedType})");
        }
        if (!hash_equals($expectedChallenge, (string)($clientData['challenge'] ?? ''))) {
            throw new WebAuthnException('Challenge mismatch — possible replay attempt');
        }
        if (($clientData['origin'] ?? '') !== $expectedOrigin) {
            throw new WebAuthnException('Origin mismatch: got ' . ($clientData['origin'] ?? '(none)') . ', expected ' . $expectedOrigin);
        }
        return $clientData;
    }

    /**
     * Parses the attestationObject from navigator.credentials.create().
     *
     * @return array{credentialId:string, publicKeyPem:string, signCount:int, aaguid:string, alg:int}
     */
    public static function parseAttestation(string $attestationObject, string $rpId): array
    {
        $decoded = Cbor::decode($attestationObject);
        if (!is_array($decoded) || !isset($decoded['authData'])) {
            throw new WebAuthnException('attestationObject: missing authData');
        }
        $authData = $decoded['authData'];
        if ($authData instanceof CborBytes) {
            $authData = $authData->value;
        }

        $parsed = self::parseAuthenticatorData($authData, $rpId, requireAttestedCredential: true);

        return [
            'credentialId' => $parsed['credentialId'],
            'publicKeyPem' => $parsed['publicKeyPem'],
            'signCount'    => $parsed['signCount'],
            'aaguid'       => $parsed['aaguid'],
            'alg'          => $parsed['alg'],
        ];
    }

    /**
     * Parses authenticatorData (registration or assertion).
     */
    public static function parseAuthenticatorData(string $authData, string $rpId, bool $requireAttestedCredential = false): array
    {
        if (strlen($authData) < 37) {
            throw new WebAuthnException('authenticatorData too short');
        }
        $rpIdHash = substr($authData, 0, 32);
        if (!hash_equals(hash('sha256', $rpId, true), $rpIdHash)) {
            throw new WebAuthnException('rpIdHash mismatch — wrong relying party id');
        }
        $flags = ord($authData[32]);
        if (!($flags & 0x01)) { // UP: user present
            throw new WebAuthnException('User Present flag not set');
        }
        $signCount = unpack('N', substr($authData, 33, 4))[1];

        $result = [
            'flags'        => $flags,
            'userVerified' => (bool)($flags & 0x04),
            'signCount'    => $signCount,
            'credentialId' => null,
            'publicKeyPem' => null,
            'aaguid'       => '',
            'alg'          => 0,
        ];

        if ($flags & 0x40) { // AT: attested credential data present
            if (strlen($authData) < 55) {
                throw new WebAuthnException('authenticatorData: attested credential data truncated');
            }
            $aaguid = substr($authData, 37, 16);
            $credIdLen = unpack('n', substr($authData, 53, 2))[1];
            if (strlen($authData) < 55 + $credIdLen) {
                throw new WebAuthnException('authenticatorData: credential id truncated');
            }
            $credentialId = substr($authData, 55, $credIdLen);
            $offset = 55 + $credIdLen;
            $coseKey = Cbor::decodeItem($authData, $offset);
            if (!is_array($coseKey)) {
                throw new WebAuthnException('Invalid COSE public key');
            }
            $result['aaguid'] = bin2hex($aaguid);
            $result['credentialId'] = $credentialId;
            $result['alg'] = (int)($coseKey[3] ?? 0);
            $result['publicKeyPem'] = self::coseKeyToPem($coseKey);
        } elseif ($requireAttestedCredential) {
            throw new WebAuthnException('Attested credential data missing from registration');
        }

        return $result;
    }

    /**
     * Converts a COSE public key (EC2/P-256 or RSA) to PEM format.
     */
    public static function coseKeyToPem(array $coseKey): string
    {
        $kty = $coseKey[1] ?? null;

        if ($kty === 2) { // EC2
            $crv = $coseKey[-1] ?? null;
            if ($crv !== 1) {
                throw new WebAuthnException('Unsupported EC curve (only P-256 is supported)');
            }
            $x = self::bytes($coseKey[-2] ?? null);
            $y = self::bytes($coseKey[-3] ?? null);
            if (strlen($x) !== 32 || strlen($y) !== 32) {
                throw new WebAuthnException('Invalid P-256 coordinates');
            }
            // SubjectPublicKeyInfo header for id-ecPublicKey / prime256v1 + uncompressed point
            $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200')
                 . "\x04" . $x . $y;
            return self::derToPem($der);
        }

        if ($kty === 3) { // RSA
            $n = self::bytes($coseKey[-1] ?? null);
            $e = self::bytes($coseKey[-2] ?? null);
            if ($n === '' || $e === '') {
                throw new WebAuthnException('Invalid RSA key parameters');
            }
            $rsaKey = self::derSequence(self::derUnsignedInt($n) . self::derUnsignedInt($e));
            $der = self::derSequence(
                self::derSequence(hex2bin('06092a864886f70d010101') . hex2bin('0500')) // rsaEncryption + NULL
                . self::derBitString($rsaKey)
            );
            return self::derToPem($der);
        }

        throw new WebAuthnException('Unsupported COSE key type: ' . var_export($kty, true));
    }

    /**
     * Verifies an assertion (navigator.credentials.get()) signature.
     */
    public static function verifyAssertion(string $authenticatorData, string $clientDataJson, string $signature, string $publicKeyPem): bool
    {
        $signedData = $authenticatorData . hash('sha256', $clientDataJson, true);
        $key = openssl_pkey_get_public($publicKeyPem);
        if ($key === false) {
            throw new WebAuthnException('Stored public key could not be loaded');
        }
        $result = openssl_verify($signedData, $signature, $key, OPENSSL_ALGO_SHA256);
        if ($result === -1) {
            throw new WebAuthnException('openssl_verify error: ' . openssl_error_string());
        }
        return $result === 1;
    }

    /** Expected origin for the current request, e.g. https://example.com[:port]. */
    public static function currentOrigin(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    /** Relying party id (hostname without port) for the current request. */
    public static function currentRpId(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return strtolower(explode(':', $host)[0]);
    }

    // ---- internal DER helpers ----

    private static function bytes(mixed $v): string
    {
        if ($v instanceof CborBytes) {
            return $v->value;
        }
        return is_string($v) ? $v : '';
    }

    private static function derLength(int $len): string
    {
        if ($len < 0x80) {
            return chr($len);
        }
        $bytes = ltrim(pack('N', $len), "\x00");
        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    private static function derSequence(string $content): string
    {
        return "\x30" . self::derLength(strlen($content)) . $content;
    }

    private static function derBitString(string $content): string
    {
        $content = "\x00" . $content; // zero unused bits
        return "\x03" . self::derLength(strlen($content)) . $content;
    }

    private static function derUnsignedInt(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '' ) {
            $bytes = "\x00";
        }
        if (ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes; // keep it positive
        }
        return "\x02" . self::derLength(strlen($bytes)) . $bytes;
    }

    private static function derToPem(string $der): string
    {
        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }
}
