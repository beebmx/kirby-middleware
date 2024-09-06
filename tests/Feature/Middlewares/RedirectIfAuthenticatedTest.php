<?php

use Beebmx\KirbyMiddleware\Facades\Middleware;
use Beebmx\KirbyMiddleware\MiddlewareGroups\GuestMiddlewareGroup;
use Beebmx\KirbyMiddleware\Middlewares\RedirectIfAuthenticated;
use Beebmx\KirbyMiddleware\Request;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;

beforeEach(function () {
    GuestMiddlewareGroup::setRoutes();
    Middleware::destroy();
});

describe('settings', function () {
    it('can set the routes by config file', function () {
        App(options: [
            'beebmx.kirby-middleware.routes' => [
                'guest' => [
                    'login',
                    'welcome',
                    'start',
                ],
            ],
        ], roots: ['index' => fixtures('tmp/guest')]);

        expect(Middleware::routes()->all()->get('guest'))
            ->toHaveCount(3);
    });

    it('can set the routes by static method in hook', function () {
        App(roots: ['index' => fixtures('tmp/guest')], hooks: [
            'system.loadPlugins:after' => function () {
                GuestMiddlewareGroup::setRoutes([
                    'login',
                    'welcome',
                    'start',
                ]);
            },
        ]);

        expect(Middleware::routes()->all()->get('guest'))
            ->toHaveCount(3);
    });

    it('can set the routes by static method before kirby for testing', function () {
        GuestMiddlewareGroup::setRoutes([
            'login',
            'welcome',
            'start',
        ]);

        App(roots: ['index' => fixtures('tmp/guest')]);

        expect(Middleware::routes()->all()->get('guest'))
            ->toHaveCount(3);
    });
});

describe('auth', function () {
    beforeEach(function () {
        $this->kirby = App(roots: ['index' => fixtures('tmp/guest')]);
    });

    it('redirects when an authenticated user request is made', function () {
        $this->kirby->impersonate('kirby', function () {
            $response = (new RedirectIfAuthenticated)->handle(new Request, function (Request $request) {
                //
            });

            expect($response)
                ->toBeInstanceOf(Response::class);
        });
    });

    it('redirects using defaults when an authenticated user request is made', function () {
        $this->kirby->impersonate('kirby', function () {
            $response = (new RedirectIfAuthenticated)->handle(new Request, function (Request $request) {
                //
            });

            expect($response->headers()['Location']->path()->toString())
                ->toEqual('dashboard');
        });
    });
});

describe('guest', function () {
    beforeEach(function () {
        $this->kirby = App(roots: ['index' => fixtures('tmp/guest')]);
    });

    it('allows to continue if a guest trigger middleware', function () {
        $response = (new RedirectIfAuthenticated)->handle(new Request, function (Request $request) {
            expect($request)
                ->toBeInstanceOf(Request::class);
        });

        expect($response)
            ->not->toBeInstanceOf(Response::class);

    });
});

describe('options', function () {
    it('can set the routes by config file', function () {
        $kirby = App(options: [
            'beebmx.kirby-middleware' => [
                'redirections' => [
                    'auth' => 'admin/dashboard',
                ],
            ],
        ], roots: ['index' => fixtures('tmp/guest')]);

        $kirby->impersonate('kirby', function () {
            $response = (new RedirectIfAuthenticated)->handle(new Request, function (Request $request) {
                //
            });

            expect($response->headers()['Location']->path()->toString())
                ->toEqual('admin/dashboard');
        });
    });
});

describe('advance', function () {
    it('can set the redirect by static method in hook using string', function () {
        $kirby = App(roots: ['index' => fixtures('tmp/auth')], hooks: [
            'system.loadPlugins:after' => function () {
                RedirectIfAuthenticated::redirectUsing('admin/dashboard');
            },
        ]);

        $kirby->impersonate('kirby', function () {
            $response = (new RedirectIfAuthenticated)->handle(new Request, function (Request $request) {
                //
            });

            expect($response->headers()['Location']->path()->toString())
                ->toEqual('admin/dashboard');
        });
    });

    it('can set the redirect by static method in hook using closure', function () {
        $kirby = App(roots: ['index' => fixtures('tmp/auth')], hooks: [
            'system.loadPlugins:after' => function () {
                RedirectIfAuthenticated::redirectUsing(fn ($request): string => 'admin/dashboard');
            },
        ]);

        $kirby->impersonate('kirby', function () {
            $response = (new RedirectIfAuthenticated)->handle(new Request, function (Request $request) {
                //
            });

            expect($response->headers()['Location']->path()->toString())
                ->toEqual('admin/dashboard');
        });
    });
});

afterAll(function () {
    Dir::remove(fixtures('tmp/guest'));
});
