<?php

namespace Beebmx\KirbyMiddleware;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Http\Request as KirbyRequest;
use Kirby\Http\Request\Body;
use Kirby\Http\Request\Query;

class Request extends KirbyRequest
{
    protected static ?Request $instance;

    public function user(): ?User
    {
        return App::instance(null, true)->user();
    }

    public function replaceBody(array $parameters = []): void
    {
        $this->body = new Body($parameters);
    }

    public function replaceQuery(array $parameters = []): void
    {
        $this->query = new Query($parameters);
    }

    public function urlIs(string $str): bool
    {
        $path = rawurldecode($this->path());

        return $path === str_replace('\*', '.*', $str);
    }

    public static function instance(array $options = []): Request
    {
        if (! isset(static::$instance)) {
            static::$instance = new self($options);
        }

        return static::$instance;
    }

    public static function replaceInstance(Request $request): Request
    {
        return static::$instance = $request;
    }

    public static function destroy(): void
    {
        static::$instance = null;
    }
}
