<?php

namespace Src\Main\View;

use Src\Main\Container\Container;
use Src\Main\Http\Request;
use Src\Main\Http\Response;

class ViewException extends \ErrorException
{
    public function report(): bool
    {
        $exception = $this->getPrevious();

        $callback = [$exception, 'report'];

        if (is_callable($callback)) {
            return Container::getInstance()->call($callback);
        }

        return false;
    }
    public function render(Request $request): ?Response
    {
        $exception = $this->getPrevious();

        if ($exception && method_exists($exception, 'render')) {
            return $exception->render($request);
        }
        return null;
    }
}
