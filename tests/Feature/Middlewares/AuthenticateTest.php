<?php

use Beebmx\KirbyMiddleware\Facades\Middleware;
use Beebmx\KirbyMiddleware\MiddlewareGroups\AuthMiddlewareGroup;
use Beebmx\KirbyMiddleware\Middlewares\Authenticate;
use Beebmx\KirbyMiddleware\Request;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;

beforeEach(function () {
    AuthMiddlewareGroup::setRoutes();
    Middleware::destroy();
});

describe('settings', function () {
    it('can set the routes by config file', function () {
        App(options: [
            'beebmx.kirby-middleware.routes' => [
                'auth' => [
                    'dashboard',
                    'dashboard/(:all)',
                ],
            ],
        ], roots: ['index' => fixtures('tmp/auth')]);

        expect(Middleware::routes()->all()->get('auth'))
            ->toHaveCount(2);
    });

    it('can set the routes by static method in hook', function () {
        App(roots: ['index' => fixtures('tmp/auth')], hooks: [
            'system.loadPlugins:after' => function () {
                AuthMiddlewareGroup::setRoutes([
                    'dashboard',
                    'dashboard/(:all)',
                ]);
            },
        ]);

        expect(Middleware::routes()->all()->get('auth'))
            ->toHaveCount(2);
    });

    it('can set the routes by static method before kirby for testing', function () {
        AuthMiddlewareGroup::setRoutes([
            'dashboard',
            'dashboard/(:all)',
        ]);

        App(roots: ['index' => fixtures('tmp/auth')]);

        expect(Middleware::routes()->all()->get('auth'))
            ->toHaveCount(2);
    });
});

describe('guest', function () {
    beforeEach(function () {
        $this->kirby = App(roots: ['index' => fixtures('tmp/auth')]);
    });

    it('redirects when an unauthenticated user request is made', function () {
        $response = (new Authenticate)->handle(new Request, function (Request $request) {});

        expect($response)
            ->toBeInstanceOf(Response::class);
    });

    it('redirects using defaults when an unauthenticated user request is made', function () {
        $response = (new Authenticate)->handle(new Request, function (Request $request) {
            //
        });

        expect($response->headers()['Location']->path()->toString())
            ->toEqual('login');
    });
});

describe('login', function () {
    beforeEach(function () {
        $this->kirby = App(roots: ['index' => fixtures('tmp/auth')]);
    });

    it('allows to continue if a user is authenticated', function () {
        $this->kirby->impersonate('kirby', function () {
            $response = (new Authenticate)->handle(new Request, function (Request $request) {
                expect($request)
                    ->toBeInstanceOf(Request::class);
            });

            expect($response)
                ->not->toBeInstanceOf(Response::class);
        });
    });
});

describe('options', function () {
    it('can set the routes by config file', function () {
        App(options: [
            'beebmx.kirby-middleware' => [
                'redirections' => [
                    'guest' => 'admin/login',
                ],
            ],
        ], roots: ['index' => fixtures('tmp/auth')]);

        $response = (new Authenticate)->handle(new Request, function (Request $request) {
            //
        });

        expect($response->headers()['Location']->path()->toString())
            ->toEqual('admin/login');
    });
});

describe('advance', function () {
    it('can set the redirect by static method in hook using string', function () {
        App(roots: ['index' => fixtures('tmp/auth')], hooks: [
            'system.loadPlugins:after' => function () {
                Authenticate::redirectUsing('admin/login');
            },
        ]);

        $response = (new Authenticate)->handle(new Request, function (Request $request) {
            //
        });

        expect($response->headers()['Location']->path()->toString())
            ->toEqual('admin/login');
    });

    it('can set the redirect by static method in hook using closure', function () {
        App(roots: ['index' => fixtures('tmp/auth')], hooks: [
            'system.loadPlugins:after' => function () {
                Authenticate::redirectUsing(fn ($request): string => 'admin/login');
            },
        ]);

        $response = (new Authenticate)->handle(new Request, function (Request $request) {
            //
        });

        expect($response->headers()['Location']->path()->toString())
            ->toEqual('admin/login');
    });
});

afterAll(function () {
    Dir::remove(fixtures('tmp/auth'));
});
