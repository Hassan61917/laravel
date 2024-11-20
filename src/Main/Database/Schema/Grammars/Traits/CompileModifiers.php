<?php

namespace Src\Main\Database\Schema\Grammars\Traits;

use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Database\Schema\Fluent;

trait CompileModifiers
{
    protected function modifyUnsigned(Blueprint $blueprint, Fluent $column): string
    {
        if ($column->unsigned) {
            return ' unsigned';
        }

        return "";
    }
    protected function modifyCollate(Blueprint $blueprint, Fluent $column): string
    {
        if ($column->collation) {
            return " collate '{$column->collation}'";
        }
        return "";
    }
    protected function modifyNullable(Blueprint $blueprint, Fluent $column): ?string
    {
        return $column->nullable ? ' null' : ' not null';
    }
    protected function modifyDefault(Blueprint $blueprint, Fluent $column): string
    {
        if ($column->default) {
            return ' default ' . $this->getDefaultValue($column->default);
        }
        return "";
    }
    protected function modifyOnUpdate(Blueprint $blueprint, Fluent $column): string
    {
        if ($column->onUpdate) {
            return ' on update ' . $this->getValue($column->onUpdate);
        }
        return "";
    }
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column): ?string
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return $blueprint->hasCommand('primary') || ($column->change && ! $column->primary)
                ? ' auto_increment'
                : ' auto_increment primary key';
        }
        return "";
    }
}
