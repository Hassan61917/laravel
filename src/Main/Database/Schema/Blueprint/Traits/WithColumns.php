<?php

namespace Src\Main\Database\Schema\Blueprint\Traits;

use Src\Main\Database\Schema\Blueprint\ColumnDefinition;
use Src\Main\Database\Schema\Builders\SchemaBuilder;
use Src\Main\Database\Schema\ForeignIdColumnDefinition;

trait WithColumns
{
    protected array $columns = [];
    public function getColumns(): array
    {
        return $this->columns;
    }
    public function foreignId(string $column): ColumnDefinition
    {
        return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
            'type' => 'bigInteger',
            'name' => $column,
            'autoIncrement' => false,
            'unsigned' => true,
        ]));
    }
    public function id(string $column = 'id'): ColumnDefinition
    {
        return $this->bigIncrements($column);
    }
    public function increments(string $column): ColumnDefinition
    {
        return $this->unsignedBigInteger($column, true);
    }
    public function bigIncrements($column): ColumnDefinition
    {
        return $this->unsignedBigInteger($column, true);
    }
    public function unsignedBigInteger(string $column, bool $autoIncrement = false): ColumnDefinition
    {
        return $this->bigInteger($column, $autoIncrement, true);
    }
    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): ColumnDefinition
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    public function double(string $column): ColumnDefinition
    {
        return $this->addColumn('double', $column);
    }
    public function char(string $column, int $length = null): ColumnDefinition
    {
        $length = $length ?: SchemaBuilder::getDefaultStringLength();

        return $this->addColumn('char', $column, compact('length'));
    }
    public function string(string $column, int $length = null): ColumnDefinition
    {
        $length = $length ?: SchemaBuilder::getDefaultStringLength();

        return $this->addColumn('string', $column, compact('length'));
    }
    public function text(string $column): ColumnDefinition
    {
        return $this->addColumn('text', $column);
    }
    public function longText(string $column): ColumnDefinition
    {
        return $this->addColumn('longText', $column);
    }
    public function boolean(string $column): ColumnDefinition
    {
        return $this->addColumn('boolean', $column);
    }
    public function enum(string $column, array $allowed): ColumnDefinition
    {
        return $this->addColumn('enum', $column, compact('allowed'));
    }
    public function date(string $column): ColumnDefinition
    {
        return $this->addColumn('date', $column);
    }
    public function dateTime(string $column, int $precision = 0): ColumnDefinition
    {
        return $this->addColumn('dateTime', $column, compact('precision'));
    }
    public function time(string $column, int $precision = 0): ColumnDefinition
    {
        return $this->addColumn('time', $column, compact('precision'));
    }
    public function timestamp(string $column, int $precision = 0): ColumnDefinition
    {
        return $this->addColumn('timestamp', $column, compact('precision'));
    }
    public function timestamps(int $precision = 0): void
    {
        $this->timestamp('created_at', $precision)->nullable();

        $this->timestamp('updated_at', $precision)->nullable();
    }
    public function nullableTimestamps(int $precision = 0): void
    {
        $this->timestamps($precision);
    }
    public function softDeletes(string $column = 'deleted_at', int $precision = 0): ColumnDefinition
    {
        return $this->timestamp($column, $precision)->nullable();
    }
    public function morphs(string $name): void
    {
        $this->numericMorphs($name);
    }
    public function nullableMorphs(string $name): void
    {
        $this->nullableNumericMorphs($name);
    }
    public function numericMorphs(string $name): void
    {
        $this->string("{$name}_type");

        $this->unsignedBigInteger("{$name}_id");

        $this->index("{$name}_type", "{$name}_id");
    }
    public function nullableNumericMorphs($name): void
    {
        $this->string("{$name}_type")->nullable();

        $this->unsignedBigInteger("{$name}_id")->nullable();

        $this->index("{$name}_type", "{$name}_id",);
    }
    public function rememberToken(): ColumnDefinition
    {
        return $this->string('remember_token', 100)->nullable();
    }
    public function addColumn(string $type, string $name, array $parameters = []): ColumnDefinition
    {
        $definition = $this->createColumn($type, $name, $parameters);

        return $this->addColumnDefinition($definition);
    }
    protected function createColumn(string $type, string $name, array $parameters): ColumnDefinition
    {
        return new ColumnDefinition(array_merge(compact('type', 'name'), $parameters));
    }
    protected function addColumnDefinition(ColumnDefinition $definition): ColumnDefinition
    {
        $this->columns[] = $definition;

        return $definition;
    }
}
