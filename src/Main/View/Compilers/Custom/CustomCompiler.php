<?php

namespace Src\Main\View\Compilers\Custom;

use Src\Main\View\Compilers\Compiler;
use Src\Main\View\Compilers\Custom\StatementCompilers\ConditionsStatements;
use Src\Main\View\Compilers\Custom\StatementCompilers\LoopsStatements;

class CustomCompiler extends Compiler
{
    protected array $statementsCompilers = [
        ConditionsStatements::class,
        LoopsStatements::class
    ];
    public function compile(string $path): void
    {
        $content = $this->read($path);
        $newContent = $this->testCompile($content);
        $this->write($this->getCompiledPath($path), $newContent);
    }
    public function testCompile(string $content): string
    {
        $content = $this->compileEcho($content);
        $result = "";
        foreach (token_get_all($content) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }
        return $result;
    }
    protected function parseToken(array $token): string
    {
        [$id, $content] = $token;
        if ($id == T_INLINE_HTML) {
            $content = $this->compileStatements($content);
        }
        return $content;
    }
    protected function compileStatements(string $content): string
    {

        preg_match_all('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $content, $matches);
        $i = 0;
        $offset = 0;
        while (isset($matches[0][$i])) {
            $match = [
                $matches[0][$i],
                $matches[1][$i],
                $matches[2][$i],
                $matches[3][$i] ?: null,
                $matches[4][$i] ?: null,
            ];
            [$content, $offset] = $this->replaceFirstStatement(
                $match[0],
                $this->compileStatement($match),
                $content,
                $offset
            );
            $i++;
        }

        return $content;
    }
    protected function replaceFirstStatement(string $search, string $replace, string $subject, int $offset): array
    {
        $position = strpos($subject, $search, $offset);

        if ($position) {
            return [
                substr_replace($subject, $replace, $position, strlen($search)),
                $position + strlen($replace),
            ];
        }

        return [$subject, 0];
    }
    protected function compileStatement(array $match): string
    {
        foreach ($this->statementsCompilers as $statementsCompiler) {
            $compiler = new $statementsCompiler();
            if ($compiler->isValid($match[1])) {
                return $compiler->compile($match[1], $match[3]);
            }
        }

        return isset($match[3]) ? $match[0] : $match[0] . $match[2];
    }
    protected function compileEcho(?string $content): string
    {
        $content = str_replace("{{", "<?php echo ", $content);
        return str_replace("}}", " ?> ", $content);
    }
}
