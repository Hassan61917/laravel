<?php

namespace Src\Main\Database\Query;

class WhereClause
{
    public ?string $operator = "=";
    public string $boolean = "and";
    public string $type = "Column";
    public string $conjunction = "where";

    public function __construct(
        public ?string $column,
        public mixed $value,
    ) {}
    public function setOperator(?string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }
    public function setBoolean(string $boolean): static
    {
        $this->boolean = $boolean;
        return $this;
    }
    public function setConjunction(string $conjunction): static
    {
        $this->conjunction = $conjunction;
        return $this;
    }
    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
    public function getConjunction(): string
    {
        return $this->conjunction;
    }
    public function getValue(): mixed
    {
        return $this->value;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getOperator(): string
    {
        return $this->operator;
    }
    public function getColumn(): string
    {
        return $this->column;
    }
    public function getBoolean(): string
    {
        return $this->boolean;
    }
}
