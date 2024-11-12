<?php

namespace Src\Main\Cookie;

use Src\Main\Support\ServiceProvider;

class CookieServiceProvider extends ServiceProvider
{
  public function getAliases(): array
  {
    return [
      "cookie" => [ICookieFactory::class, CookieJar::class]
    ];
  }
  public function register(): void
  {
    $this->app->singleton("cookie", function ($app) {
      $config = $app["config"]["session"];
      return new CookieJar($config);
    });
  }
}
