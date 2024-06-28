<?php

use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyMiddleware\MiddlewareGroups\WebMiddlewareGroup;
use Tests\Fixtures\Group\EmptyMiddlewareGroup;

it('set a default name if none is registered')
    ->expect(new WebMiddlewareGroup)
    ->name
    ->toBeNull()
    ->name()
    ->toBeString()
    ->toEqual('web');

it('returns all the routes available')
    ->expect(new WebMiddlewareGroup)
    ->getKirbyMiddlewareRoutes()
    ->toBeArray()
    ->toBeEmpty();

it('has a default route path for web middleware')
    ->expect(new WebMiddlewareGroup)
    ->routes()
    ->toBeString()
    ->toEqual('(:all)');

it('returns the paths for currend middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.routes' => [
            'web' => [
                'blog/(:any)',
                'content/(:any)',
            ],
        ],
    ]);

    expect(new WebMiddlewareGroup)
        ->routes()
        ->toBeArray();
});

it('can change the default route paths', function () {
    App(options: [
        'beebmx.kirby-middleware.routes' => [
            'web' => [
                'blog/(:any)',
                'content/(:any)',
            ],
        ],
    ]);

    expect(new WebMiddlewareGroup)
        ->routes()
        ->toBeArray()
        ->toEqual([
            'blog/(:any)',
            'content/(:any)',
        ]);
});

it('doesnt matter if the key is uppercase', function () {
    App(options: [
        'beebmx.kirby-middleware.routes' => [
            'WEB' => 'blog/(:any)',
        ],
    ]);

    expect(new WebMiddlewareGroup)
        ->routes()
        ->toBeString()
        ->toEqual('blog/(:any)');
});

it('can be added a new group by using the options setting', function () {
    Middleware::destroy();

    App(options: [
        'beebmx.kirby-middleware.groups' => [
            EmptyMiddlewareGroup::class,
        ],
    ]);

    expect(Middleware::instance()->getMiddlewareGroups())
        ->toBeArray()
        ->toHaveKey('empty');
});
