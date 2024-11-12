<?php

namespace Src\Main\Session;

use SessionHandlerInterface;
use Src\Main\Encryption\Exceptions\DecryptException;
use Src\Main\Encryption\IEncryptor;

class EncryptedStore extends SessionStore
{
    public function __construct(
        string $name,
        SessionHandlerInterface $handler,
        protected IEncryptor $encryptor,
    ) {
        parent::__construct($name, $handler);
    }
    protected function prepareForUnserialize($data): string
    {
        try {
            return $this->encryptor->decrypt($data);
        } catch (DecryptException) {
            return $this->serialization === 'json' ? json_encode([]) : serialize([]);
        }
    }
    protected function prepareForStorage(string $data): string
    {
        return $this->encryptor->encrypt($data);
    }
    public function getEncryptor(): IEncryptor
    {
        return $this->encryptor;
    }
}
