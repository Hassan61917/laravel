<?php

namespace Src\Main\Debug\Traits;

use Exception;
use Illuminate\Support\Arr;
use Src\Main\Auth\Exceptions\AuthenticationException;
use Src\Main\Auth\Exceptions\AuthorizationException;
use Src\Main\Database\Exceptions\Eloquent\ModelNotFoundException;
use Src\Main\Database\Exceptions\Eloquent\RecordsNotFoundException;
use Src\Main\Http\Exceptions\AccessDeniedException;
use Src\Main\Http\Exceptions\HttpException;
use Src\Main\Http\Exceptions\NotFoundException;
use Src\Main\Http\Exceptions\ServerErrorException;
use Src\Main\Http\JsonResponse;
use Src\Main\Http\Redirect\RedirectResponse;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Session\TokenMismatchException;
use Src\Main\Support\ViewErrorBag;
use Src\Main\Validation\ValidationException;
use Src\Main\View\View;
use Throwable;

trait RenderRequest
{
    protected function renderRequest(Request $request, Throwable $e): Response
    {
        $e = $this->prepareException($e);

        return match (true) {
            $e instanceof ValidationException => $this->convertValidationExceptionToResponse($request, $e),
            $e instanceof AuthenticationException => $this->unauthenticated($request, $e),
            default => $this->renderExceptionResponse($request, $e),
        };
    }
    protected function prepareException(Throwable $e): Throwable
    {
        return match (true) {
            $e instanceof ModelNotFoundException => new NotFoundException($e->getMessage()),
            $e instanceof AuthorizationException => new AccessDeniedException($e),
            $e instanceof TokenMismatchException => new HttpException($e->getMessage(), 419),
            $e instanceof RecordsNotFoundException => new NotFoundException('Not found.'),
            default => $e,
        };
    }
    protected function renderExceptionResponse(Request $request, Throwable $e): Response
    {
        return $this->shouldReturnJson($request)
            ? $this->prepareJsonResponse($request, $e)
            : $this->prepareResponse($request, $e);
    }
    protected function convertValidationExceptionToResponse(Request $request, ValidationException $e): Response
    {
        if (isset($e->reseponse)) {
            return $e->getResponse();
        }

        return $this->shouldReturnJson($request)
            ? $this->invalidJson($request, $e)
            : $this->invalid($request, $e);
    }
    protected function shouldReturnJson(Request $request): bool
    {
        return $request->expectsJson();
    }
    protected function invalid(Request $request, ValidationException $exception): RedirectResponse
    {
        return redirect($exception->redirectTo ?? url()->previous())
            ->withInput($request->input())
            ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag));
    }
    protected function invalidJson(Request $request, ValidationException $exception): Response
    {
        return responseFactory()->json([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->getStatusCode());
    }
    protected function prepareJsonResponse(Request $request, Throwable $e): JsonResponse
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
        );
    }
    protected function convertExceptionToArray(Throwable $e): array
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(fn($trace) => Arr::except($trace, ['args']))->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
    protected function isHttpException(Throwable $e): bool
    {
        return $e instanceof HttpException;
    }
    protected function prepareResponse(Request $request, Throwable $e): Response
    {
        if (! $this->isHttpException($e) && config('app.debug')) {
            return $this->convertExceptionToResponse($e);
        }

        if (! $this->isHttpException($e)) {
            $e = new ServerErrorException($e->getMessage());
        }

        return $this->renderHttpException($e);
    }
    protected function convertExceptionToResponse(Throwable $e): Response
    {
        return new Response(
            $this->renderExceptionContent($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }
    protected function renderExceptionContent(Throwable $e): string
    {
        return $this->renderer->render($e);
    }
    protected function renderHttpException(HttpException $e): Response
    {
        $view = $this->getExceptionView($e);

        try {
            return response($view->render(), $e->getStatusCode(), $e->getHeaders());
        } catch (Exception $e) {
            return $this->convertExceptionToResponse($e);
        }
    }
    protected function getExceptionView(HttpException $e): View
    {
        $code = $e->getStatusCode();

        $data = [
            'errors' => new ViewErrorBag(),
            'exception' => $e,
        ];

        $view = "errors-{$code}";

        if ($this->viewManager->exists($view)) {
            return $this->viewManager->make($view, $data);
        }

        $path = $this->getExceptionViewPath($code);

        if (!file_exists($path)) {
            $path = $this->getExceptionViewPath(500);
        }

        return $this->viewManager->file($path, $data);
    }
    protected function getExceptionViewPath(int $code): string
    {
        return dirname(__DIR__) . "/views/{$code}.custom.php";
    }
    protected function unauthenticated(Request $request, AuthenticationException $exception): Response
    {
        return $this->shouldReturnJson($request)
            ? responseFactory()->json(['message' => $exception->getMessage()], 401)
            : redirector()->guest($exception->redirectTo($request) ?? route('login'));
    }
}
