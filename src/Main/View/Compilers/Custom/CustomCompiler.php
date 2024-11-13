<?php

namespace Src\Main\View\Compilers\Custom;

use Src\Main\View\Compilers\Compiler;

class CustomCompiler extends Compiler
{
    public function compile(string $path): void
    {
        $content = $this->read($path);
        $newContent = $this->compileContent($content);
        $this->write($this->getCompiledPath($path), $newContent);
    }
    protected function compileContent(?string $content): string
    {
        $content = str_replace("{{", "<?php echo ", $content);
        return str_replace("}}", " ?> ", $content);
    }
}
