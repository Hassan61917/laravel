<?php

namespace Src\Main\Database\Schema\Grammars\Traits;

use Src\Main\Database\Schema\Blueprint\ColumnDefinition;

trait CompileTypes
{
    protected function typeChar(ColumnDefinition $column): string
    {
        return "char({$column->length})";
    }
    protected function typeString(ColumnDefinition $column): string
    {
        return "varchar({$column->length})";
    }
    protected function typeEnum(ColumnDefinition $column): string
    {
        $allowed = $this->quoteString($column->allowed);
        return "enum({$allowed})";
    }
    protected function typeTime(ColumnDefinition $column): string
    {
        return $column->precision ? "time($column->precision)" : 'time';
    }
    protected function typeDateTime(ColumnDefinition $column): string
    {
        $this->setCurrentTime($column);

        return $column->precision ? "datetime($column->precision)" : 'datetime';
    }
    protected function typeTimestamp(ColumnDefinition $column): string
    {
        $this->setCurrentTime($column);

        return $column->precision ? "timestamp($column->precision)" : 'timestamp';
    }
    protected function setCurrentTime(ColumnDefinition $column): void
    {
        $current = $column->precision ? "CURRENT_TIMESTAMP($column->precision)" : 'CURRENT_TIMESTAMP';

        if ($column->useCurrent) {
            $column->default($current);
        }

        if ($column->useCurrentOnUpdate) {
            $column->onUpdate($current);
        }
    }
}
