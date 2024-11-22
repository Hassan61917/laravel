<?php

namespace Src\Main\Database;

interface IRelationParser
{
    public function parse(array $relations): array;
}
