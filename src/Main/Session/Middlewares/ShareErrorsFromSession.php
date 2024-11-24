<?php

namespace Src\Main\Session\Middlewares;

use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Support\ViewErrorBag;
use Src\Main\View\ViewManager;

class ShareErrorsFromSession extends Middleware
{
    public function __construct(
        protected ViewManager $view
    ) {}
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        $this->view->share(
            'errors',
            $request->session()->get('errors') ?: new ViewErrorBag()
        );

        return null;
    }
}
