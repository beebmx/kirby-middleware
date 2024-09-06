<?php

namespace Beebmx\KirbyMiddleware;

use Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup;
use Beebmx\Pipeline\Pipeline;
use Closure;
use Kirby\Cms\App as Kirby;
use Kirby\Exception\Exception;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;
use Laravel\SerializableClosure\SerializableClosure;

class Middleware
{
    protected array $appends = [];

    protected array $globals = [];

    protected array $groupAppends = [];

    protected array $groupPrepends = [];

    protected array $groupRemovals = [];

    protected array $groups = [];

    protected array $prepends = [];

    protected array $removals = [];

    protected bool $shouldSkipMiddleware;

    protected mixed $response = null;

    protected RouteCollection $routes;

    protected static ?Middleware $instance;

    protected Kirby $kirby;

    public function __construct()
    {
        $this->kirby = Kirby::instance();

        if (! isset(static::$instance)) {
            static::$instance = $this;
        }

        $this->setup();
    }

    protected function setup(): void
    {
        $this
            ->setShouldSkipMiddleware()
            ->setRoutes()
            ->setGlobalMiddleware()
            ->setMiddlewareGroups()
            ->setWebMiddlewares()
            ->setAuthMiddlewares()
            ->setGuestMiddlewares();
    }

    protected function setShouldSkipMiddleware(): static
    {
        $this->shouldSkipMiddleware = ! $this->kirby->option('beebmx.kirby-middleware.enabled', true);

        return $this;
    }

    protected function setRoutes(): static
    {
        $this->routes = new RouteCollection($this->kirby->option('beebmx.kirby-middleware.routes', []));

        return $this;
    }

    protected function setGlobalMiddleware(): static
    {
        $this->globals = $this->kirby->option('beebmx.kirby-middleware.global', []);

        return $this;
    }

    protected function setMiddlewareGroups(): static
    {
        $defaults = [
            \Beebmx\KirbyMiddleware\MiddlewareGroups\WebMiddlewareGroup::class,
            \Beebmx\KirbyMiddleware\MiddlewareGroups\AuthMiddlewareGroup::class,
            \Beebmx\KirbyMiddleware\MiddlewareGroups\GuestMiddlewareGroup::class,
        ];

        $groups = $this->kirby->option('beebmx.kirby-middleware.groups', []);

        $middlewares = array_merge($defaults, $groups);

        try {
            foreach ($middlewares as $middleware) {
                $this->addClassToGroup($middleware);
            }
        } catch (Exception $exception) {
            //
        }

        return $this;
    }

    protected function setWebMiddlewares(): static
    {
        $this->appendToGroup('web', $this->kirby->option('beebmx.kirby-middleware.web', []));

        return $this;
    }

    protected function setAuthMiddlewares(): static
    {
        $this->appendToGroup('auth', $this->kirby->option('beebmx.kirby-middleware.auth', []));

        return $this;
    }

    protected function setGuestMiddlewares(): static
    {
        $this->appendToGroup('guest', $this->kirby->option('beebmx.kirby-middleware.guest', []));

        return $this;
    }

    public function handle(Request $request)
    {
        $middleware = $this->shouldSkipMiddleware ? [] : $this->resolveMiddleware($request->path()->toString());

        try {
            $this->response = (new Pipeline)
                ->send($request)
                ->through($middleware)
                ->then(
                    fn (Request $request) => Request::replaceInstance($request)
                );
        } catch (Exception $exception) {
            $this->response = $exception;
        }

        return $this->response;
    }

    public function resolveMiddleware(string $path): array
    {
        return array_merge($this->getGlobalMiddleware(), $this->gatherRouteMiddleware($path));
    }

    public function gatherRouteMiddleware(string $path): array
    {
        return static::flatten(
            $this->getMiddlewareGroupsBy(
                $this->routes->resolve($path)
            )
        );
    }

    public function prepend(array|string|Closure $middleware): static
    {
        $this->prepends = array_merge(
            A::wrap($middleware),
            $this->prepends
        );

        return $this;
    }

    public function append(array|string|Closure $middleware): static
    {

        $this->appends = array_merge(
            $this->appends,
            A::wrap($middleware)
        );

        return $this;
    }

    public function remove(array|string|Closure $middleware): static
    {
        $this->removals = array_merge(
            $this->removals,
            A::wrap($middleware)
        );

        return $this;
    }

    public function group(string $group, array $middleware): static
    {
        $this->groups[$group] = $middleware;

        return $this;
    }

    public function prependToGroup(string $group, array|string|Closure $middleware): static
    {
        $this->groupPrepends[$group] = array_merge(
            A::wrap($middleware),
            $this->groupPrepends[$group] ?? []
        );

        return $this;
    }

    public function appendToGroup(string $group, array|string|Closure $middleware): static
    {
        $this->groupAppends[$group] = array_merge(
            $this->groupAppends[$group] ?? [],
            A::wrap($middleware)
        );

        return $this;
    }

    public function removeFromGroup(string $group, array|string|Closure $middleware): static
    {
        $this->groupRemovals[$group] = array_merge(
            A::wrap($middleware),
            $this->groupRemovals[$group] ?? []
        );

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addClassToGroup(string $middleware): static
    {
        try {
            $middlewareGroup = new $middleware;
        } catch (Exception $exception) {
            throw new Exception("Class [$middleware] does not exist.");
        }

        if ($middlewareGroup instanceof MiddlewareGroup) {
            $this->group($middlewareGroup->name(), $middlewareGroup->group());
            $this->routes->add([$middlewareGroup->name() => $middlewareGroup->routes()]);
        } else {
            throw new Exception("Middleware [$middleware] should be extends from \\Beebmx\\Kirby\\Middleware\\MiddlewareGroup");
        }

        return $this;
    }

    public function getGlobalMiddleware(): array
    {
        $globals = [
            \Beebmx\KirbyMiddleware\Middlewares\TrimStrings::class,
        ];

        $globals = array_merge($globals, $this->globals);

        return array_values(
            array_filter(
                array_udiff(
                    array_unique(array_merge($this->prepends, $globals, $this->appends), SORT_REGULAR),
                    $this->removals,
                    fn ($a, $b) => strcmp(
                        $a instanceof Closure ? serialize(SerializableClosure::unsigned($a)) : $a,
                        $b instanceof Closure ? serialize(SerializableClosure::unsigned($b)) : $b
                    )
                )
            )
        );
    }

    public function getMiddlewareGroups(): array
    {
        $middleware = [...$this->groups];

        foreach ($this->groupRemovals as $group => $removals) {
            $middleware[$group] = array_values(array_filter(
                array_udiff(
                    $middleware[$group] ?? [],
                    $removals,
                    fn ($a, $b) => strcmp(
                        $a instanceof Closure ? serialize(SerializableClosure::unsigned($a)) : $a,
                        $b instanceof Closure ? serialize(SerializableClosure::unsigned($b)) : $b
                    )
                )
            ));
        }

        foreach ($this->groupPrepends as $group => $prepends) {
            $middleware[$group] = array_values(array_filter(
                array_unique(array_merge($prepends, $middleware[$group] ?? []), SORT_REGULAR)
            ));
        }

        foreach ($this->groupAppends as $group => $appends) {
            $middleware[$group] = array_values(array_filter(
                array_unique(array_merge($middleware[$group] ?? [], $appends), SORT_REGULAR)
            ));
        }

        return $middleware;
    }

    public function getMiddlewareGroupsBy(string|array $keys): array
    {
        $groups = $this->getMiddlewareGroups();

        if (is_string($keys) && in_array($keys, array_keys($groups))) {
            return [$keys => $groups[$keys]];
        }

        $middlewareGroups = [];

        if (is_array($keys)) {
            foreach ($groups as $key => $middleware) {
                if (in_array($key, $keys)) {
                    $middlewareGroups[$key] = $middleware;
                }
            }
        }

        return $middlewareGroups;
    }

    public function hasMiddlewareGroup(string $group): bool
    {
        return in_array($group, array_keys($this->groups));
    }

    public function routes(): RouteCollection
    {
        return $this->routes;
    }

    public function hasMiddleware($middleware): bool
    {
        return in_array($middleware, $this->getGlobalMiddleware());
    }

    public function response(): mixed
    {
        if ($this->response) {
            return $this->response;
        }

        return $this->handle(Request::instance());
    }

    public static function instance(): Middleware
    {
        if (! isset(static::$instance)) {
            static::$instance = new self;
        }

        return static::$instance;
    }

    public static function destroy(): void
    {
        static::$instance = null;
    }

    public static function flatten($array, $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}
