<?php

namespace Src\Main\Foundation\Bootstraps;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Src\Main\Foundation\Application;
use Src\Main\Support\Env;

class LoadEnvironment implements IBootstrap
{
    public function bootstrap(Application $app): void
    {
        try {
            $this->createEnv($app)->safeLoad();
        } catch (InvalidFileException $e) {
            $this->writeError($e);
        }
    }
    protected function createEnv(Application $app): Dotenv
    {
        return Dotenv::create(
            Env::getRepository(),
            $app->environmentPath(),
            $app->environmentFile()
        );
    }
    protected function writeError(InvalidFileException $e): void
    {
        http_response_code(500);

        exit(1);
    }
}
