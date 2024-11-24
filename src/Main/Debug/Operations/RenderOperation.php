<?php

namespace Src\Main\Debug\Operations;

use Src\Main\Console\AppCommand;
use Src\Main\Container\IContainer;
use Src\Main\Debug\IExceptionOperation;
use Src\Main\Debug\IExceptionRenderer;
use Src\Main\Debug\Traits\RenderCommand;
use Src\Main\Debug\Traits\RenderRequest;
use Src\Main\Http\Request;
use Src\Main\View\ViewManager;
use Throwable;

class RenderOperation implements IExceptionOperation
{
    use RenderRequest,
        RenderCommand;

    public function __construct(
        protected IContainer $container,
        protected ViewManager $viewManager,
        protected IExceptionRenderer $renderer,
    ) {}
    public function setRenderer(IExceptionRenderer $renderer): static
    {
        $this->renderer = $renderer;
        return $this;
    }
    public function handleRequest(Request $request, Throwable $e): void
    {
        $response = $this->renderRequest($request, $e);

        $this->container->instance("exception.response", $response);
    }
    public function handleCommand(AppCommand $command, Throwable $e): void
    {
        $this->renderCommand($command, $e);
    }
}
