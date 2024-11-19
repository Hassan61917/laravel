<?php

namespace Src\Main\View\Compilers\Custom\StatementCompilers;

class LoopsStatements extends StatementCompiler
{
    protected array $statements = [
        "for",
        "foreach",
        "while",
    ];
}
