<?php

namespace Src\Main\Debug;

use Src\Main\Container\IContainer;
use Src\Symfony\Finder\Finder;

class OperationLoader implements IOperationLoader
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function load(): array
    {
        $operations = $this->findOperations();

        $result = [];

        foreach ($operations as $operation) {
            $instance = $this->container->make($operation);

            $this->container->instance($operation, $instance);

            $result[] = $instance;
        }

        return $result;
    }
    protected function findOperations(): array
    {
        $operationFiles = Finder::create()
            ->in(__DIR__ . "/Operations")
            ->name(".php");

        $result = [];

        foreach ($operationFiles as $file) {
            $result[] = find_class($file->getRealPath());
        }

        return $result;
    }
}
