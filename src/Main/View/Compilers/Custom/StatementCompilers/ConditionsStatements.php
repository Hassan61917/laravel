<?php

namespace Src\Main\View\Compilers\Custom\StatementCompilers;

class ConditionsStatements extends StatementCompiler
{
    protected array $statements = [
        "if",
        "else",
        "elseif"
    ];
    protected array $statementResults = [
        "auth" => "(auth()->guard()->check())",
        "endAuth" => "endif"
    ];
}
