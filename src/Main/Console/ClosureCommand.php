<?php

namespace Src\Main\Console;

use Closure;
use ReflectionFunction;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class ClosureCommand extends AppCommand
{
    public function __construct(
        string $signature,
        protected Closure $callback
    ) {
        $this->signature = $signature;
        parent::__construct();
    }
    protected function execute(IConsoleInput $input, IConsoleOutput $output): int
    {
        $inputs = array_merge($input->getArguments(), $input->getOptions());

        $parameters = [];

        foreach ((new ReflectionFunction($this->callback))->getParameters() as $parameter) {
            if (isset($inputs[$parameter->getName()])) {
                $parameters[$parameter->getName()] = $inputs[$parameter->getName()];
            }
        }

        return (int) $this->laravel->call([$this->callback->bindTo($this, $this)], $parameters);
    }
    public function purpose(string $description): static
    {
        $this->setDescription($description);

        return $this;
    }
}
