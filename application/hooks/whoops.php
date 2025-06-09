<?php

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class WhoopsHook
{
    public function bootWhoops()
    {
        if (
            empty($_ENV['APP_ENVIRONMENT'])
            || $_ENV['APP_ENVIRONMENT'] !== 'development'
            || ! class_exists(Run::class)
        ) {
            return;
        }

        $handler = null;
        if (php_sapi_name() === 'cli') {
            $handler = new PlainTextHandler();
        } else {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $handler = str_contains($path, '/api')
                ? new JsonResponseHandler()
                : new PrettyPageHandler();
        }

        $whoops = new Run();
        $whoops->pushHandler($handler);
        $whoops->register();
    }
}
