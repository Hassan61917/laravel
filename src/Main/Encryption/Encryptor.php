<?php

namespace Src\Main\Encryption;

use RuntimeException;
use Src\Main\Encryption\Exceptions\DecryptException;
use Src\Main\Encryption\Exceptions\EncryptException;

class Encryptor implements IEncryptor, IStringEncryptor
{
    protected static array $supportedCiphers = [
        'aes-128-cbc' => ['size' => 16, 'aead' => false],
        'aes-256-cbc' => ['size' => 32, 'aead' => false],
        'aes-128-gcm' => ['size' => 16, 'aead' => true],
        'aes-256-gcm' => ['size' => 32, 'aead' => true],
    ];
    protected string $key;
    protected string $cipher;
    public function __construct(
        string $key,
        string $cipher = 'AES-256-CBC'
    ) {
        if (! static::supported($key, $cipher)) {
            $ciphers = implode(', ', array_keys(self::$supportedCiphers));

            throw new RuntimeException("Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}.");
        }
        $this->key = $key;
        $this->cipher = $cipher;
    }
    public static function supported(string $key, string $cipher): bool
    {
        $cipher = strtolower($cipher);

        if (! isset(self::$supportedCiphers[$cipher])) {
            return false;
        }

        return mb_strlen($key, '8bit') === self::$supportedCiphers[$cipher]['size'];
    }
    public static function generateKey(string $cipher): string
    {
        return random_bytes(self::$supportedCiphers[strtolower($cipher)]['size'] ?? 32);
    }
    public function encrypt(mixed $value, bool $serialize = true): string
    {
        $cipher = strtolower($this->cipher);

        $iv = random_bytes(openssl_cipher_iv_length($cipher));

        $value = openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $cipher,
            $this->key,
            0,
            $iv,
            $tag
        );

        if (!$value) {
            throw new EncryptException('Could not encrypt the data.');
        }

        $iv = base64_encode($iv);

        $tag = base64_encode($tag ?? '');

        $mac = self::$supportedCiphers[$cipher]['aead'] ? '' : $this->hash($iv, $value, $this->key);

        $json = json_encode(compact('iv', 'value', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }
    public function decrypt(string $payload, bool $unserialize = true): mixed
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $tag = empty($payload['tag']) ? null : base64_decode($payload['tag']);

        $this->ensureTagIsValid($tag);

        $decrypted = openssl_decrypt(
            $payload['value'],
            strtolower($this->cipher),
            $this->key,
            0,
            $iv,
            $tag ?? ''
        );

        if (!$decrypted) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }
    public function getKey(): string
    {
        return $this->key;
    }
    public function encryptString(string $value): string
    {
        return $this->encrypt($value, false);
    }
    public function decryptString(string $payload): string
    {
        return $this->decrypt($payload, false);
    }
    protected function ensureTagIsValid(?string $tag): void
    {
        $cipher = strtolower($this->cipher);

        if (self::$supportedCiphers[$cipher]['aead'] && strlen($tag) !== 16) {
            throw new DecryptException('Could not decrypt the data.');
        }

        if ($tag && ! self::$supportedCiphers[$cipher]['aead']) {
            throw new DecryptException('Unable to use tag because the cipher algorithm does not support AEAD.');
        }
    }
    protected function getJsonPayload(string $payload): array
    {
        $decodedPayload = json_decode(base64_decode($payload), true) ?? [];

        if (! $this->validPayload($decodedPayload)) {
            throw new DecryptException('The payload is invalid.');
        }

        if (! self::$supportedCiphers[strtolower($this->cipher)]['aead'] && ! $this->validMac($decodedPayload)) {
            throw new DecryptException('The MAC is invalid.');
        }

        return $decodedPayload;
    }
    protected function validMac(array $payload): bool
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['value'], $this->key),
            $payload['mac']
        );
    }
    protected function validPayload(array $payload): bool
    {
        foreach (['iv', 'value', 'mac'] as $item) {
            if (! isset($payload[$item]) || ! is_string($payload[$item])) {
                return false;
            }
        }

        if (isset($payload['tag']) && ! is_string($payload['tag'])) {
            return false;
        }

        return strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length(strtolower($this->cipher));
    }
    protected function hash(string $iv, mixed $value, string $key): string
    {
        return hash_hmac('sha256', $iv . $value, $key);
    }
}
