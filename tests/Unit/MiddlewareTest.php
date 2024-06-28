<?php

use Beebmx\KirbyMiddleware\Http\TrimStrings;
use Beebmx\KirbyMiddleware\Http\ValidateCsrfToken;
use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyMiddleware\Request;
use Beebmx\KirbyMiddleware\RouteCollection;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Tests\Fixtures\Group\EmptyMiddlewareGroup;
use Tests\Fixtures\Group\NotInstanceOfMiddlewareGroup;
use Tests\Fixtures\Group\TestMiddlewareGroup;
use Tests\Fixtures\Middleware\EmptyMiddleware;
use Tests\Fixtures\Middleware\OtherMiddleware;

beforeEach(function () {
    Middleware::destroy();
});

describe('singleton', function () {
    it('can create an instance', function () {
        $middleware = new Middleware;

        expect($middleware)
            ->toBeInstanceOf(Middleware::class);
    });

    it('set the instance', function () {
        $middleware = Middleware::instance();

        expect($middleware)
            ->toBeInstanceOf(Middleware::class);
    });

    it('returns the same instance from new instance creation', function () {
        (new Middleware)
            ->append(EmptyMiddleware::class);

        expect(Middleware::instance()->getGlobalMiddleware())
            ->toBeArray()
            ->toContain(EmptyMiddleware::class);
    });
});

describe('global', function () {
    beforeEach(function () {
        $this->middleware = new Middleware;
    });

    it('can prepend middleware', function () {
        $this->middleware->prepend(EmptyMiddleware::class);

        expect($this->middleware->getGlobalMiddleware())
            ->toBeArray()
            ->toContain(EmptyMiddleware::class);
    });

    it('can append middleware', function () {
        $this->middleware->append(EmptyMiddleware::class);

        expect($this->middleware->getGlobalMiddleware())
            ->toBeArray()
            ->toContain(EmptyMiddleware::class);
    });

    it('can remove middleware', function () {
        $this->middleware->append(EmptyMiddleware::class);
        $this->middleware->remove(EmptyMiddleware::class);

        expect($this->middleware->getGlobalMiddleware())
            ->toBeArray()
            ->not->toContain(EmptyMiddleware::class);
    });

    it('can prepend a closure middleware', function () {
        $this->middleware
            ->prepend(function (Request $request, Closure $next) {
                return Response::redirect('login');
            });

        $middleware = $this->middleware->getGlobalMiddleware();

        expect(current($middleware))
            ->toBeInstanceOf(Closure::class);
    });

    it('can append a closure middleware', function () {
        $this->middleware
            ->append(function (Request $request, Closure $next) {
                return Response::redirect('login');
            });

        $middleware = $this->middleware->getGlobalMiddleware();

        expect(end($middleware))
            ->toBeInstanceOf(Closure::class);
    });

    it('determine if a middleware is in globalMiddleware', function () {
        $this->middleware->append(EmptyMiddleware::class);

        expect($this->middleware)
            ->hasMiddleware(EmptyMiddleware::class)->toBeTrue()
            ->hasMiddleware(OtherMiddleware::class)->toBeFalse();
    });
});

describe('groups', function () {
    beforeEach(function () {
        $this->middleware = (new Middleware)
            ->group('test', [
                EmptyMiddleware::class,
            ]);
    });

    it('can prepend group middleware', function () {
        $this->middleware->prependToGroup('test', OtherMiddleware::class);

        expect($this->middleware->getMiddlewareGroups()['test'])
            ->toBeArray()
            ->toContain(OtherMiddleware::class);
    });

    it('can append group middleware', function () {
        $this->middleware->appendToGroup('test', OtherMiddleware::class);

        expect($this->middleware->getMiddlewareGroups()['test'])
            ->toBeArray()
            ->toContain(OtherMiddleware::class);
    });

    it('can remove group middleware', function () {
        $this->middleware->removeFromGroup('test', EmptyMiddleware::class);

        expect($this->middleware->getMiddlewareGroups()['test'])
            ->toBeArray()
            ->not->toContain(EmptyMiddleware::class);
    });

    it('can prepend a closure group middleware', function () {
        $this->middleware
            ->prependToGroup('test', function (Request $request, Closure $next) {
                return Response::redirect('login');
            });

        $middleware = $this->middleware->getMiddlewareGroups()['test'];

        expect(current($middleware))
            ->toBeInstanceOf(Closure::class);
    });

    it('can append a closure group middleware', function () {
        $this->middleware
            ->appendToGroup('test', function (Request $request, Closure $next) {
                return Response::redirect('login');
            });

        $middleware = $this->middleware->getMiddlewareGroups()['test'];

        expect(end($middleware))
            ->toBeInstanceOf(Closure::class);
    });

    it('determine if a middleware is in middlewareGroups')
        ->expect(fn () => $this->middleware)
        ->hasMiddlewareGroup('web')->toBeTrue()
        ->hasMiddlewareGroup('missing')->toBeFalse();

    it('returns groups by string keys')
        ->expect(fn () => $this->middleware)
        ->getMiddlewareGroupsBy('web')
        ->toBeArray()
        ->toHaveKey('web')
        ->not->toHaveKey('test');

    it('wont returns anything if key doesnt exists')
        ->expect(fn () => $this->middleware)
        ->getMiddlewareGroupsBy('ignored')
        ->toBeArray()
        ->toBeEmpty();

    it('returns groups by array keys')
        ->expect(fn () => $this->middleware)
        ->getMiddlewareGroupsBy(['web', 'ignored'])
        ->toBeArray()
        ->toHaveKey('web')
        ->not->toHaveKeys(['test', 'ignored']);

});

describe('class groups', function () {
    beforeEach(function () {
        $this->middleware = new Middleware;
    });

    it('loads the default WebMiddlewareGroups')
        ->expect(fn () => $this->middleware->hasMiddlewareGroup('web'))
        ->toBeTrue();

    it('add routes from default WebMiddlewareGroups')
        ->expect(fn () => $this->middleware->routes()->toArray())
        ->toHaveKey('web');

    it('throws an error if is not instance of MiddlewareGroup', function () {
        $this->middleware->addClassToGroup(NotInstanceOfMiddlewareGroup::class);
    })->throws(Exception::class);

    it('can add class to middleware groups', function () {
        $this->middleware->addClassToGroup(EmptyMiddlewareGroup::class);

        expect($this->middleware->hasMiddlewareGroup('empty'))
            ->toBeTrue();
    });
});

describe('routes', function () {
    it('can returns the routes from middleware')
        ->expect(new Middleware)
        ->routes()
        ->toBeInstanceOf(RouteCollection::class);

});

describe('resolve middleware groups', function () {
    beforeEach(function () {
        $this->middleware = (new Middleware)
            ->addClassToGroup(TestMiddlewareGroup::class);
    });

    it('flatten middlewares')
        ->expect(fn () => $this->middleware->gatherRouteMiddleware(path: 'any-path'))
        ->toBeArray()
        ->not->toHaveKey('web')
        ->toContain(ValidateCsrfToken::class);

    it('gather middleware routes by path')
        ->expect(fn () => $this->middleware)
        ->gatherRouteMiddleware('any-path')
        ->toContain(ValidateCsrfToken::class)
        ->not->toContain(EmptyMiddleware::class)
        ->gatherRouteMiddleware('test/me')
        ->toContain(ValidateCsrfToken::class, EmptyMiddleware::class, OtherMiddleware::class)
        ->gatherRouteMiddleware('tests/10')
        ->toContain(ValidateCsrfToken::class, EmptyMiddleware::class, OtherMiddleware::class)
        ->gatherRouteMiddleware('testing/specific/page')
        ->toContain(ValidateCsrfToken::class, EmptyMiddleware::class, OtherMiddleware::class)
        ->gatherRouteMiddleware('tests/me')
        ->not->toContain(EmptyMiddleware::class, OtherMiddleware::class)
        ->gatherRouteMiddleware('testing/other/page')
        ->not->toContain(EmptyMiddleware::class, OtherMiddleware::class);

    it('includes global middlewares when resolving it')
        ->expect(fn () => $this->middleware)
        ->resolveMiddleware('any-path')
        ->toContain(TrimStrings::class);

    it('includes groups middleware when resolving it')
        ->expect(fn () => $this->middleware)
        ->resolveMiddleware('any-path')
        ->toContain(ValidateCsrfToken::class);
});
