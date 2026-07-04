<?php
/**
 * CredentialStore — tiny JSON-file storage for registered passkeys.
 *
 * For production, replace this with your database: you only need to store,
 * per credential: credentialId (base64url), username, publicKeyPem, signCount.
 */

declare(strict_types=1);

final class CredentialStore
{
    public function __construct(private readonly string $file) {}

    /** @return array<string, array> credentialId(base64url) => credential */
    public function all(): array
    {
        if (!is_file($this->file)) {
            return [];
        }
        $data = json_decode((string)file_get_contents($this->file), true);
        return is_array($data) ? $data : [];
    }

    public function get(string $credentialId): ?array
    {
        return $this->all()[$credentialId] ?? null;
    }

    /** @return array<string, array> credentials belonging to a user */
    public function forUser(string $username): array
    {
        return array_filter($this->all(), fn($c) => ($c['username'] ?? '') === $username);
    }

    public function save(string $credentialId, array $credential): void
    {
        $all = $this->all();
        $all[$credentialId] = $credential;
        $this->write($all);
    }

    /** True if the storage file (or its directory) can be written by PHP. */
    public function isWritable(): bool
    {
        if (is_file($this->file)) {
            return is_writable($this->file);
        }
        $dir = dirname($this->file);
        return is_dir($dir) ? is_writable($dir) : is_writable(dirname($dir));
    }

    public function delete(string $credentialId): bool
    {
        $all = $this->all();
        if (!isset($all[$credentialId])) {
            return false;
        }
        unset($all[$credentialId]);
        $this->write($all);
        return true;
    }

    private function write(array $all): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            throw new RuntimeException("Cannot create storage directory '{$dir}' — check filesystem permissions");
        }
        $written = @file_put_contents(
            $this->file,
            json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
        if ($written === false) {
            throw new RuntimeException(
                "Cannot write '{$this->file}' — make it writable by the web server "
                . "(e.g. chmod 775 " . dirname($this->file) . ")"
            );
        }
    }
}
