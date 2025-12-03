<?php

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class WhoopsHook
{
    public function bootWhoops()
    {
        $whoopsEnabled = empty($_ENV['WHOOPS_ERROR_PAGE_ENABLED'])
            ? true
            : filter_var($_ENV['WHOOPS_ERROR_PAGE_ENABLED'], FILTER_VALIDATE_BOOLEAN);

        if (! $whoopsEnabled) {
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

        if ($handler instanceof PrettyPageHandler) {
            $sensitiveVars = $this->extractEnvNames(APPPATH . '.env');

            foreach ($sensitiveVars as $sensitiveVar) {
                $handler->blacklist('_SERVER', $sensitiveVar);
                $handler->blacklist('_ENV', $sensitiveVar);
            }
        }

        $whoops = new Run();
        $whoops->pushHandler($handler);
        $whoops->register();
    }

    private function extractEnvNames(string $filePath)
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            return [];
        }

        $envNames = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || trim($line) === '') {
                continue;
            }

            $parts = explode('=', $line, 2);

            if (isset($parts[0])) {
                $envNames[] = trim($parts[0]);
            }
        }

        return $envNames;
    }
}
