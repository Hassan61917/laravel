<?php

namespace Src\Main\Debug\Operations;

use Illuminate\Support\Arr;
use Src\Main\Console\AppCommand;
use Src\Main\Debug\ExceptionHandleable;
use Src\Main\Debug\IExceptionOperation;
use Src\Main\Http\Exceptions\HttpException;
use Src\Main\Http\Request;
use Src\Main\Log\ILogger;
use Throwable;

class ReportOperation implements IExceptionOperation
{
    protected array $reportCallbacks = [];
    protected array $levels = [];
    protected array $dontReport = [];
    protected array $internalDontReport = [
        HttpException::class
    ];
    public function __construct(
        protected ILogger $logger
    ) {}
    public function getLogger(): ILogger
    {
        return $this->logger;
    }
    public function dontReport(Throwable $exception): static
    {
        $this->dontReport[] = $exception;
        return $this;
    }
    public function addReportCallback(callable $callback): static
    {
        $this->reportCallbacks[] = $callback;

        return $this;
    }
    public function stopIgnoring(Throwable $exception): static
    {
        $this->dontReport = array_filter($this->dontReport, fn($e) => $e != $exception);

        return $this;
    }
    public function handleRequest(Request $request, Throwable $e): void
    {
        $this->report($request, $e);
    }
    public function handleCommand(AppCommand $command, Throwable $e): void
    {
        $this->report($command, $e);
    }
    protected function report(ExceptionHandleable $item, Throwable $e): void
    {
        if ($this->shouldNotReport($e)) {
            return;
        }

        $this->reportException($item, $e);
    }
    protected function shouldNotReport(Throwable $e): bool
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport);

        return Arr::first($dontReport, fn($type) => $e instanceof $type) !== null;
    }
    protected function reportException(ExceptionHandleable $item, Throwable $e): void
    {
        foreach ($this->reportCallbacks as $reportCallback) {
            if (!$reportCallback($e)) {
                return;
            }
        }

        $logger = $this->getLogger();

        $level = Arr::first(
            $this->levels,
            fn($level, $type) => $e instanceof $type,
            "error"
        );

        $context = $this->context($item, $e);

        method_exists($logger, $level)
            ? $logger->{$level}($e->getMessage(), $context)
            : $logger->log($level, $e->getMessage(), $context);
    }
    protected function context(ExceptionHandleable $item, Throwable $e): array
    {
        try {
            return array_filter([
                'UserId' => auth()->id(),
                "Source" => get_class($item),
                $e
            ]);
        } catch (Throwable) {
            return [];
        }
    }
}
