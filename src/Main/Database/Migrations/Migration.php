<?php

namespace Src\Main\Database\Migrations;

abstract class Migration
{
    public bool $withinTransaction = true;
    protected ?string $connection = null;
    public function getConnection(): ?string
    {
        return $this->connection;
    }
    public abstract function up(): void;
    public abstract function down(): void;
}
