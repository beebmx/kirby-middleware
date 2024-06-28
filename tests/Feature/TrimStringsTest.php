<?php

use Beebmx\KirbyMiddleware\Http\TrimStrings;
use Beebmx\KirbyMiddleware\Request;

describe('basic', function () {
    beforeEach(function () {
        $this->kirby = App(request: [
            'body' => [
                'title' => 'This is a body title',
                'spaced' => ' Body text contains space ',
                'ignored' => ' Body text ignored with space ',
                'other' => ' Other body text ignored with space ',
            ],
            'query' => [
                'title' => 'This is a query title',
                'spaced' => ' Query text ignored with space ',
                'ignored' => ' Query text ignored with space ',
            ],
        ]);

        $this->request = new Request([
            'method' => $this->kirby->request()->method(),
            'body' => $this->kirby->request()->body(),
            'query' => $this->kirby->request()->query(),
            'url' => $this->kirby->request()->url(),
        ]);

        $this->middleware = new TrimStrings;
    });

    it('returns the same string in body if not requires trim', function () {
        $this->middleware->handle($this->request, function (Request $request) {
            expect($request->body()->get('title'))
                ->toBe('This is a body title');
        });
    });

    it('trims body content when requires', function () {
        $this->middleware->handle($this->request, function (Request $request) {
            expect($request->body()->get('spaced'))
                ->toBe('Body text contains space');
        });
    });

    it('returns the same string in query if not requires trim', function () {
        $this->middleware->handle($this->request, function (Request $request) {
            expect($request->query()->get('title'))
                ->toBe('This is a query title');
        });
    });

    it('trims query content when requires', function () {
        $this->middleware->handle($this->request, function (Request $request) {
            expect($request->query()->get('spaced'))
                ->toBe('Query text ignored with space');
        });
    });

    it('wont trim a specific input and set statically', function () {
        TrimStrings::except(['ignored']);

        $this->middleware->handle($this->request, function (Request $request) {
            expect($request)
                ->body()->get('ignored')
                ->toBe(' Body text ignored with space ')
                ->query()->get('ignored')
                ->toBe(' Query text ignored with space ');
        });
    });
});

describe('options', function () {
    it('can ignore inputs', function () {
        $kirby = App(options: [
            'beebmx.kirby-middleware.exceptions' => [
                'trim' => [
                    'secured',
                ],
            ],
        ], request: [
            'body' => [
                'secured' => ' This is a body title ',
                'other' => ' This is a body title ',
            ],
        ]);

        $request = new Request([
            'body' => $kirby->request()->body(),
        ]);

        (new TrimStrings)->handle($request, function (Request $request) {
            expect($request)
                ->body()->get('other')
                ->toBe('This is a body title')
                ->body()->get('secured')
                ->toBe(' This is a body title ');
        });
    });
});
