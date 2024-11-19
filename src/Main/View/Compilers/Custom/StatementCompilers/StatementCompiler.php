<?php

namespace Src\Main\View\Compilers\Custom\StatementCompilers;

abstract class StatementCompiler
{
    protected array $statements = [];
    protected array $statementResults = [];
    public function isValid(string $statement): bool
    {
        return $this->hasStatement($statement);
    }
    public function compile(string $statement, ?string $expression): string
    {
        $method = "compile" . ucfirst($statement);
        if (method_exists($this, $method)) {
            return $this->$method($expression);
        }

        return $this->compileStatement($statement, $expression ?? "");
    }
    protected function parseStatement(string $statement): string
    {
        $statement = strtolower($statement);
        return str_starts_with($statement, "end")
            ? substr($statement, 3)
            : $statement;
    }
    protected function hasStatement(string $statement): bool
    {
        $statement = $this->parseStatement($statement);

        return in_array($statement, $this->statements) || $this->hasResult($statement);
    }
    protected function hasResult(string $statement): bool
    {
        return isset($this->statementResults[$statement]);
    }
    protected function getResult(string $statement): string
    {
        return $this->statementResults[$statement];
    }
    protected function getStatement(string $key): ?string
    {
        if ($this->hasResult($key)) {
            return $this->getResult($key);
        }

        return in_array($this->parseStatement($key), $this->statements) ? $key : null;
    }
    protected function getEnd(string $statement): string
    {
        if ($this->hasStatement($statement)) {
            $value = $this->getStatement($statement);
            if ($value) {
                return str_starts_with($value, "end") ? $value : "end" . $value;
            }
        }

        return $statement;
    }
    protected function compilePhp(string $expression): string
    {
        return "<?php $expression ?>";
    }
    protected function compileStatement(string $statement, string $expression): string
    {
        if ($this->hasStatement($statement)) {
            if (str_contains($statement, "end")) {
                return $this->compilePhp($this->getEnd($statement) . ";");
            }
            return $this->compilePhp($this->getStatement($statement) . $expression . ":");
        }

        return $statement;
    }
}
