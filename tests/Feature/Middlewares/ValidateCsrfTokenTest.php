<?php

use Beebmx\KirbyMiddleware\Exception\TokenMismatchException;
use Beebmx\KirbyMiddleware\Middlewares\ValidateCsrfToken;
use Beebmx\KirbyMiddleware\Request;
use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Str;

beforeEach(function () {
    if (Str::endsWith(App::instance()->roots()->index(), 'tmp/token') && Dir::exists(fixtures('tmp/token'))) {
        App::instance()->session()->destroy();
    }
});

describe('basic', function () {
    it('wont throw an error if a requests is made from reading methods and token is not present', function () {
        $request = new Request([
            'method' => 'GET',
        ]);

        $middleware = new ValidateCsrfToken;
        $middleware->handle($request, fn (Request $request) => $request);
    })->throwsNoExceptions();

    it('throw an error if a requests is made from required methods and token is not present', function () {
        App(roots: ['index' => fixtures('tmp/token')]);

        $request = new Request([
            'method' => 'POST',
        ]);

        $middleware = new ValidateCsrfToken;
        $middleware->handle($request, fn (Request $request) => $request);
    })->throws(TokenMismatchException::class);
});

describe('validation', function () {
    it('validate token from request content', function (string $field) {
        $kirby = App(roots: ['index' => fixtures('tmp/token')]);

        $request = new Request([
            'method' => 'POST',
            'body' => [$field => $kirby->csrf()],
        ]);

        $middleware = new ValidateCsrfToken;
        $middleware->handle($request, fn (Request $request) => $request);
    })
        ->with(['csrf', 'csrf-token', 'x-csrf', 'x-csrf-token', '_token'])
        ->throwsNoExceptions();
});

describe('options', function () {
    it('can ignore paths', function () {
        $kirby = App(options: [
            'beebmx.kirby-middleware.exceptions' => [
                'csrf' => [
                    'test',
                    'blog',
                ],
            ],
        ], request: ['method' => 'POST', 'url' => '/test',
        ], children: [['slug' => 'home'], ['slug' => 'test'],
        ], roots: ['index' => fixtures('tmp/token')]);

        $kirby->page('test')->render();
    })->throwsNoExceptions();
});

afterAll(function () {
    Dir::remove(fixtures('tmp/site'));
    Dir::remove(fixtures('tmp/token'));
});
