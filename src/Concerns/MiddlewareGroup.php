<?php

namespace Beebmx\KirbyMiddleware\Concerns;

use Kirby\Cms\App;
use Kirby\Toolkit\Str;

trait MiddlewareGroup
{
    public string $name;

    public array $group;

    public string|array $routes = '';

    public function name(): string
    {
        return $this->name ?? static::default();
    }

    public function routes(): string|array
    {
        return array_key_exists($this->name(), $this->getParseRouteKeys())
                ? $this->getRouteByName()
                : $this->routes;
    }

    public function group(): array
    {
        return $this->group;
    }

    public function getKirbyMiddlewareRoutes()
    {
        return App::instance()->option('beebmx.kirby-middleware.routes', []);
    }

    protected function getRouteByName()
    {
        $routes = $this->getParseRouteKeys();

        return $routes[$this->name()];
    }

    protected function getParseRouteKeys()
    {
        return array_change_key_case($this->getKirbyMiddlewareRoutes());
    }

    public static function default(): string
    {
        $name = static::getClassBasename(get_called_class());

        return Str::endsWith($name, 'MiddlewareGroup')
            ? Str::lower(Str::replace($name, 'MiddlewareGroup', ''))
            : Str::lower($name);
    }

    protected static function getClassBasename($class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}
