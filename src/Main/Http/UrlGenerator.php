<?php

namespace Src\Main\Http;

use Src\Main\Http\Exceptions\NotFoundException;
use Src\Main\Routing\IRouteCollection;
use Src\Main\Routing\Route\Route;
use Src\Main\Session\ISessionStore;
use Src\Main\Support\Traits\InteractsWithTime;
use Src\Main\Utils\Str;

class UrlGenerator
{
    use InteractsWithTime;
    protected string $forcedRoot;
    protected string $forceScheme;
    protected ?string $cachedRoot = null;
    protected ?string $cachedScheme = null;
    protected ?RouteUrlGenerator $routeGenerator;
    protected Request $request;
    public function __construct(
        protected IRouteCollection $routes,
        Request $request,
        protected ISessionStore $session,
        protected string $appKey,
        protected ?string $assetRoot = null
    ) {
        $this->setRequest($request);
    }
    public function full(): string
    {
        return $this->request->fullUrl();
    }
    public function isValidUrl(string $path): bool
    {
        if (! preg_match('~^(#|//|https?://|(mailto|tel|sms):)~', $path)) {
            return filter_var($path, FILTER_VALIDATE_URL) !== false;
        }

        return true;
    }
    public function current(): string
    {
        return $this->to($this->request->getPathInfo());
    }
    public function previous(bool $fallback = false): string
    {
        $referrer = $this->request->getHeaders()->get('referer');

        $url = $referrer ? $this->to($referrer) : $this->getPreviousUrlFromSession();

        if ($url) {
            return $url;
        } elseif ($fallback) {
            return $this->to($fallback);
        }

        return $this->to('/');
    }
    public function previousPath(bool $fallback = false): string
    {
        $previousPath = str_replace($this->to('/'), '', rtrim(preg_replace('/\?.*/', '', $this->previous($fallback)), '/'));

        return $previousPath === '' ? '/' : $previousPath;
    }
    public function formatRoot(string $scheme, ?string $root = null): string
    {
        if (!$root) {
            if (!$this->cachedRoot) {
                $this->cachedRoot = isset($this->forcedRoot) ?: $this->request->url();
            }

            $root = $this->cachedRoot;
        }
        $start = str_starts_with($root, 'http://') ? 'http://' : 'https://';

        return preg_replace('~' . $start . '~', $scheme, $root, 1);
    }
    public function formatScheme(bool $secure = false): ?string
    {
        if ($secure) {
            return 'https://';
        }

        if (is_null($this->cachedScheme)) {
            $this->cachedScheme = $this->forceScheme ?? $this->request->getScheme() . '://';
        }

        return $this->cachedScheme;
    }
    public function format(string $root, string $path): string
    {
        $path = '/' . trim($path, '/');

        return trim($root . $path, '/');
    }
    public function to(string $path, array $extra = [], bool $secure = false): string
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $tail = implode('/', array_map('rawurlencode', $extra));

        $root = $this->formatRoot($this->formatScheme($secure));


        [$path, $query] = $this->extractQueryString($path);

        return $this->format(
            $root,
            '/' . trim($path . '/' . $tail, '/')
        ) . $query;
    }
    public function secure(string $path, array $parameters = []): string
    {
        return $this->to($path, $parameters, true);
    }
    public function asset(string $path, bool $secure = false): string
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $this->assetRoot ?: $this->formatRoot($this->formatScheme($secure));

        return Str::finish($this->removeIndex($root), '/') . trim($path, '/');
    }
    public function secureAsset(string $path): string
    {
        return $this->asset($path, true);
    }
    public function assetFrom(string $root, string $path, bool $secure = false): string
    {
        $root = $this->formatRoot($this->formatScheme($secure), $root);

        return $this->removeIndex($root) . '/' . trim($path, '/');
    }
    public function hasValidSignature(Request $request, bool $absolute = true, array $ignoreQuery = []): bool
    {
        return $this->hasCorrectSignature($request, $absolute, $ignoreQuery)
            && $this->signatureHasNotExpired($request);
    }
    public function hasValidRelativeSignature(Request $request, array $ignoreQuery = []): bool
    {
        return $this->hasValidSignature($request, false, $ignoreQuery);
    }
    public function hasCorrectSignature(Request $request, bool $absolute = true, array $ignoreQuery = []): bool
    {
        $ignoreQuery[] = 'signature';

        $url = $absolute ? $request->url() : '/' . $request->path();

        $queryString = collect(explode('&', $request->getServer()->get('QUERY_STRING')))
            ->reject(fn($parameter) => in_array(Str::before($parameter, '='), $ignoreQuery))
            ->join('&');

        $original = rtrim($url . '?' . $queryString, '?');

        $signature = hash_hmac('sha256', $original, $this->appKey);

        return hash_equals($signature, $request->query('signature', ''));
    }
    public function signatureHasNotExpired(Request $request): bool
    {
        $expires = $request->query('expires');

        return ! ($expires && $this->currentTime() > $expires);
    }
    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $route = $this->routes->getByName($name);

        if ($route) {
            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new NotFoundException("Route [{$name}] not defined.");
    }
    public function toRoute(Route $route, array $parameters, bool $absolute = true): string
    {
        return $this->routeUrl()->to($route, $parameters, $absolute);
    }
    public function defaults(array $defaults): void
    {
        $this->routeUrl()->defaults($defaults);
    }
    public function getDefaultParameters(): array
    {
        return $this->routeUrl()->defaultParameters;
    }
    public function forceScheme(?string $scheme): void
    {
        $this->cachedScheme = null;

        $this->forceScheme = $scheme ? $scheme . '://' : null;
    }
    public function forceRootUrl(?string $root): void
    {
        $this->forcedRoot = $root ? rtrim($root, '/') : null;

        $this->cachedRoot = null;
    }
    public function getRequest(): Request
    {
        return $this->request;
    }
    public function setRequest(Request $request): void
    {
        $this->request = $request;

        $this->cachedRoot = null;
        $this->cachedScheme = null;

        $this->routeGenerator = null;
    }
    protected function extractQueryString(string $path): array
    {
        $queryPosition = strpos($path, '?');

        if ($queryPosition) {
            return [
                substr($path, 0, $queryPosition),
                substr($path, $queryPosition),
            ];
        }

        return [$path, ''];
    }
    protected function removeIndex(string $root): string
    {
        $i = 'index.php';

        return str_contains($root, $i) ? str_replace('/' . $i, '', $root) : $root;
    }
    protected function routeUrl(): RouteUrlGenerator
    {
        if (!isset($this->routeGenerator)) {
            $this->routeGenerator = new RouteUrlGenerator($this, $this->request);
        }

        return $this->routeGenerator;
    }
    protected function getPreviousUrlFromSession(): ?string
    {
        return $this->session->previousUrl();
    }
}
