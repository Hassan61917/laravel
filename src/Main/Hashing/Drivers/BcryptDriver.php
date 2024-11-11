<?php

namespace Src\Main\Hashing\Drivers;

use RuntimeException;

class BcryptDriver extends HashDriver
{
    protected int $rounds = 12;
    protected bool $verifyAlgorithm = false;
    public function __construct(
        protected array $options = []
    ) {
        $this->rounds = $options['rounds'] ?? $this->rounds;
        $this->verifyAlgorithm = $options['verify'] ?? $this->verifyAlgorithm;
    }
    public function setRounds(int $rounds): static
    {
        $this->rounds = $rounds;

        return $this;
    }
    public function make(string $value, array $options = []): string
    {
        $hash = password_hash(
            $value,
            PASSWORD_BCRYPT,
            ['cost' => $this->cost($options),]
        );

        if (!$hash) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }
    public function needsRehash(string $hash, array $options = []): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
    }
    public function check(string $value, string $hash, array $options = []): bool
    {
        if ($this->verifyAlgorithm && ! $this->isUsingCorrectAlgorithm($hash)) {
            throw new RuntimeException('This password does not use the Bcrypt algorithm.');
        }

        return parent::check($value, $hash, $options);
    }
    public function verifyConfiguration(string $value): bool
    {
        return $this->isUsingCorrectAlgorithm($value) && $this->isUsingValidOptions($value);
    }
    protected function cost(array $options = []): int
    {
        return $options['rounds'] ?? $this->rounds;
    }
    protected function isUsingCorrectAlgorithm(string $hash): bool
    {
        return $this->info($hash)['algoName'] === 'bcrypt';
    }
    protected function isUsingValidOptions(string $hash): bool
    {
        ['options' => $options] = $this->info($hash);

        if (! is_int($options['cost'] ?? null)) {
            return false;
        }

        if ($options['cost'] > $this->rounds) {
            return false;
        }

        return true;
    }
}
