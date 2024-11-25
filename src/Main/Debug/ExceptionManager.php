<?php

namespace Src\Main\Debug;

use Src\Main\Container\IContainer;
use Src\Main\Debug\Operations\ReportOperation;
use Src\Main\Debug\Operations\RenderOperation;
use Throwable;

class ExceptionManager
{
    public function __construct(
        protected IContainer $container,
        protected ExceptionHandler $exceptionHandler
    ) {}
    public function addOperation(IExceptionOperation $operation): static
    {
        $this->exceptionHandler->addOperation($operation);
        return $this;
    }
    public function handleOperation(string $name, Throwable $e): void
    {
        if ($this->container->bound("command")) {
            $item = $this->container->make("command");
        } else {
            $item = $this->container->make("request");
        }

        $this->exceptionHandler->handleOperation($name, $item, $e);
    }
    public function handleReport(Throwable $e): void
    {
        $this->handleOperation("report", $e);
    }
    public function dontReport(Throwable $exception): static
    {
        $this->getReportOperation()->dontReport($exception);
        return $this;
    }
    public function stopIgnoring(Throwable $exception): static
    {
        $this->getReportOperation()->stopIgnoring($exception);
        return $this;
    }
    public function changeRenderer(IExceptionRenderer $renderer): static
    {
        $this->getRenderOperation()->setRenderer($renderer);
        return $this;
    }
    public function reportCallback(callable $callable): static
    {
        $this->getReportOperation()->addReportCallback($callable);
        return $this;
    }
    protected function getReportOperation(): ReportOperation
    {
        return $this->container->make(ReportOperation::class);
    }
    protected function getRenderOperation(): RenderOperation
    {
        return $this->container->make(ReportOperation::class);
    }
}
