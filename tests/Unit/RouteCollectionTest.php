<?php

use Beebmx\KirbyMiddleware\Route;
use Beebmx\KirbyMiddleware\RouteCollection;
use Kirby\Toolkit\Collection;

it('creates a collection of routes', function () {
    $route = new RouteCollection(['web' => '*']);

    expect($route->all())
        ->toBeInstanceOf(Collection::class);
});

it('returns an array of all routes', function () {
    $route = new RouteCollection(['web' => '*']);

    expect($route->toArray())
        ->toBeArray();
});

it('creates a collection of routes for every item', function () {
    $route = new RouteCollection([
        'web' => '(:all)',
        'tests' => [
            'test/*',
            'tests/*',
            'testing/pages/*',
        ],
    ]);

    expect($route->all()->first())
        ->toBeInstanceOf(Route::class);
});

it('resolves groups if a path is in the pattern', function () {
    $route = new RouteCollection([
        'web' => '(:all)',
        'other' => [
            'principal/(:num)',
            'all/(:all)',
        ],
        'tests' => [
            'test/(:all)',
            'tests/(:num?)',
            'testing/specific/page',
        ],
    ]);

    expect($route->resolve('tests'))
        ->toBeArray()
        ->toContain('web', 'tests')
        ->not->toContain('other');
});

it('resolves a group of paths without patterns', function () {
    $route = new RouteCollection([
        'web' => '(:all)',
        'patrol' => [
            '/',
            'blog',
            'blog/content-1',
            'blog/content-2',
            'others',
            'welcome',
        ],
    ]);

    expect($route)
        ->resolve('/')
        ->toContain('web', 'patrol')
        ->resolve('blog')
        ->toContain('web', 'patrol')
        ->resolve('blog/content-1')
        ->toContain('web', 'patrol')
        ->resolve('other')
        ->toContain('web')
        ->not->toContain('patrol');
});

it('can add new array of routes to collection', function () {
    $route = new RouteCollection([
        'web' => '(:all)',
        'tests' => [
            'test/(:all)',
            'tests/(:num?)',
            'testing/specific/page',
        ],
    ]);

    $route->add([
        'patrol' => [
            'blog',
            'pages',
            'welcome',
        ],
    ]);

    expect($route->toArray())
        ->toHaveKeys(['web', 'tests', 'patrol']);
});

it('can add new string of routes to collection', function () {
    $route = new RouteCollection([
        'web' => '(:all)',
        'tests' => [
            'test/(:all)',
        ],
    ]);

    $route->add(['patrol' => '(:all)']);

    expect($route->toArray())
        ->toHaveKeys(['web', 'tests', 'patrol']);
});
