<?php

namespace Src\Main\Debug;

use Exception;
use Throwable;

class ExceptionHandler implements IExceptionHandler
{
    protected array $operations = [];
    public function __construct(
        protected IOperationLoader $operationLoader
    ) {}
    public function addOperation(IExceptionOperation $operation): static
    {
        $this->operations[] = $operation;
        return $this;
    }
    public function handleOperation(string $name, ExceptionHandleable $item, Exception $e): void
    {
        $operation = collect($this->operations)->first(
            fn($op) => str_contains(strtolower(get_class($op)), $name)
        );

        $item->handleException($operation, $e);
    }
    public function handle(ExceptionHandleable $item, Throwable $e): void
    {
        $operations = $this->getOperations();

        foreach ($operations as $operation) {
            $item->handleException($operation, $e);
        }
    }
    protected function getOperations(): array
    {
        return array_merge(
            $this->operations,
            $this->operationLoader->load()
        );
    }
}
