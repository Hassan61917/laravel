<?php

namespace Src\Main\Database\Connections;

use Src\Main\Database\Query\Grammars\MysqlQueryGrammar;
use Src\Main\Database\Query\Processors\MySqlQueryProcessor;
use Src\Main\Database\Schema\Builders\MysqlSchemaBuilder;
use Src\Main\Database\Schema\Grammars\MysqlSchemaGrammar;

class MysqlConnection extends Connection
{
    public function getSchemaBuilder(): MysqlSchemaBuilder
    {
        return new MysqlSchemaBuilder($this);
    }
    protected function getDefaultQueryProcessor(): MysqlQueryProcessor
    {
        return new MysqlQueryProcessor();
    }
    protected function getDefaultQueryGrammar(): MysqlQueryGrammar
    {
        return new MysqlQueryGrammar();
    }
    protected function getDefaultSchemaGrammar(): MysqlSchemaGrammar
    {
        return new MysqlSchemaGrammar();
    }
}
