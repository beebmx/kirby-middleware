<p align="center">
<a href="https://github.com/beebmx/kirby-middleware/actions"><img src="https://img.shields.io/github/actions/workflow/status/beebmx/kirby-middleware/tests.yml?branch=main" alt="Build Status"></a>
<a href="https://packagist.org/packages/beebmx/kirby-middleware"><img src="https://img.shields.io/packagist/dt/beebmx/kirby-middleware" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/beebmx/kirby-middleware"><img src="https://img.shields.io/packagist/v/beebmx/kirby-middleware" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/beebmx/kirby-middleware"><img src="https://img.shields.io/packagist/l/beebmx/kirby-middleware" alt="License"></a>
</p>

# Kirby Middleware

`Kirby Middleware` provides a powerful mechanism for inspecting and filtering requests entering your `Kirby` site.

****

## Overview

- [1. Installation](#installation)
- [2. Usage](#usage)
- [3. Middleware](#middleware)
- [4. Options](#options)
- [5. Facades](#facades)
- [6. Plugins](#plugins)
- [7. Roadmap](#roadmap)
- [8. License](#license)
- [9. Credits](#credits)

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby-middleware`.

### Composer

```
composer require beebmx/kirby-middleware
```

## Usage

Out of the box, you don't need to do anything to start using (except for installation).
When you install the `Kirby Middleware` package, it comes with two ways of management middlewares, `global` middlewares and `groups` of middlewares.


### Global middlewares

This middleware will always be triggered in every `Page` by the `Middleware` handler.
Out of the box comes with a `TrimStrings` middleware, which will remove spaces in the `Request` made.

> [!NOTE]
> To access to this request, you should call the `Beebmx\KirbyMiddleware\Request` instance.
>
> The `Kirby\Http\Request` instance will never be modified.

You can access to the `Request` instance transformed with:

```php
use Beebmx\KirbyMiddleware\Request;

$request = Request::instance();
```

You can add features to the `global` middleware in your `config.php` file:

```php
'beebmx.kirby-middleware' => [
    'global' => [
        MyOwnGlobalMiddleware::class,
    ],
],
```

> [!NOTE]
> You can add as much middleware as requested.
>
> They can be a `class` or a `Closure`.


#### TrimStrings

`TrimStrings` clean all the inputs in the request, but sometimes you need to ignore some `inputs` to be trimmed; you can skip it with:

```php
'beebmx.kirby-middleware' => [
    'exceptions' => [
        'trim' => [
            'password',
            'password_confirmation',
        ],
    ],
],
```

And you can recover those `inputs` with the `Request` instance in your controllers, models or any place required with:

```php
use Beebmx\KirbyMiddleware\Request;

Request::instance()->get('yourInput')
```

Or for your convinience you can use the facade:

```php
use Beebmx\KirbyMiddleware\Facades\Request;

Request::get('yourInput')
```

### Group middlewares

The group middlewares will depend on routes to be triggered. By default, the group middleware comes with the `web`, `auth` and `guest` middleware, it brings a `ValidateCsrfToken` middlewares.

You can set the routes by adding the `routes` values in your `config.php` file:

```php
'beebmx.kirby-middleware' => [
    'routes' => [
        'web' => [
            'blog/(:any)',
            'content/(:alpha)',
            'page/(:num)',
        ]
    ],
],
```

> [!NOTE]
> You can add a [pattern](https://getkirby.com/docs/reference/router/patterns) like any `Kirby` route
>
> By default, the `web` group comes with the `(:all)` route.
>
> The `auth` and `guest` middlewares are inactive by default, but you can customize the routes to enable them.

And of course, you can add more features to the `web` middleware in your `config.php` file:

```php
'beebmx.kirby-middleware' => [
    'web' => [
        MyOwnMiddleware::class,
    ],
],
```

If the `web` group is not what you need, you can add a new group of middleware. You can add it within the `config.php` file:

```php
'beebmx.kirby-middleware' => [
    'groups' => [
        MyOwnMiddlewareGroup::class,
    ],
],
```

The `Middleware Group` should looks like:

```php
use Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup;

class MyOwnMiddlewareGroup extends MiddlewareGroup
{
    public string $name = 'review';

    public string|array|null $routes = [
        'blog/(:any)',
        'content/(:alpha)',
    ];

    public array $group = [
        ReviewBlogMiddleware::class,
        ReviewContentMiddleware::class,
        ReviewByAuthorMiddleware::class,
    ];
}
```

> [!IMPORTANT]
> All the group middleware classes should extend `Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup` class.

#### ValidateCsrfToken

When you use an HTML form with `POST`, `PUT`, `PATCH`, or `DELETE` in your template, you should include a hidden CSRF `_token` field in the form so that the CSRF protection middleware can validate the request.

```html
<form method="POST" action="myPage">
    <input type="hidden" name="_token" value="<?= csrf() ?>" />
</form>
```

> [!NOTE]
> For convenience, you can also use `csrf`, `csrf-token` or `_token`.

Sometimes you need to ignore some `routes` from the CSRF validation; you can skip it with:

```php
'beebmx.kirby-middleware' => [
    'exceptions' => [
        'csrf' => [
            'payment',
            'test',
        ],
    ],
],
```

### Security middlewares

`Kirby Middleware` comes with two (`auth` and `guest`) middlewares to improve your security flow based on user authentication.

#### Auth middleware

The `auth` middleware provides a starting point to validate if the user is authenticated and if the user is able to visit given routes. If not, it will redirect to some `URL` to perform a proper login.

Heres an example of it:

```php
'beebmx.kirby-middleware' => [
    'routes' => [
      'auth' => [
            'dashboard',
            'dashboard/(:all)',
            'logout',
        ],
    ],
    'redirections' => [
        'guest' => 'login',
    ],
],
```

> [!NOTE]
> If the user is not authenticated, the middleware will redirect to a `guest` page.

#### Guest middleware

The `guest` middleware provides a starting point to validate if the visitor is a guest and is unauthenticated. If the user is authenticated, it will redirect to some `URL` to be inside a secured welcome page or dashboard.

Heres an example of it:

```php
'beebmx.kirby-middleware' => [
    'routes' => [
        'guest' => [
            'login',
        ],
    ],
    'redirections' => [
        'auth' => 'dashboard',
    ],
],
```

> [!NOTE]
> If the user is authenticated, the middleware will redirect to a `auth` page.


## Middleware

When you create a middleware, you can use a `class` or a `Closure`; it will depend on your needs and complexity.

### Middleware class

When you create your own middleware class, it should look like:

```php
use Beebmx\KirbyMiddleware\Request;
use Closure;

class ValidateSomeInformation
{
    public function handle(Request $request, Closure $next)
    {
        // Perform action

        return $next($request);
    }
}
```

As you can see, `handle` requires two parameters: a `Request` called `$request` and a `Closure` called `$next`.
The `$request` contains the current request made in Kirby by the hook `route:before`.

The second parameter `$next`, you should call it at the end of the process to proceed to the next middleware validation with the `$request`.

If you need, some validations can prevent to continue with any other validation; you can throw an error or make a response redirection:

```php
use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Http\Response;

class UserShouldBeAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if(empty($request->user())) {
            return Response::redirect('login')
        }

        return $next($request);
    }
}
```

Or with an exception:

```php
use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Exception\ErrorPageException;

class UserShouldBeAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if(empty($request->user())) {
            return throw new ErrorPageException([
                'fallback' => 'Unauthorized',
                'httpCode' => 401,
            ]);
        }

        return $next($request);
    }
}
```

### Closure middleware

The easiest way to add a `global`, `web`, `auth` or `guest` middleware is with a `Closure`; when you add a closure, it should look like:

```php
use Beebmx\KirbyMiddleware\Request;
use Closure;

'beebmx.kirby-middleware' => [
    'web' => [
        function (Request $request, Closure $next) {
            // Perform action

            return $next($request);
        },
    ],
],
```

> [!IMPORTANT]
> Remember to call the `$next` closure to proceed to the next validation with the `$request`.

## Options

| Option                               | Default |  Type   | Description                                                       |
|:-------------------------------------|:-------:|:-------:|:------------------------------------------------------------------|
| beebmx.kirby-middleware.enabled      |  true   | `bool`  | Enable/Disable all `Kirby Middleware`.                            |
| beebmx.kirby-middleware.exceptions   |   []    | `array` | Set exceptions for `trim` and `csrf` middlewares.                 |
| beebmx.kirby-middleware.global       |   []    | `array` | Add your own `global` middlewares.                                |
| beebmx.kirby-middleware.groups       |   []    | `array` | Add your own `groups` middlewares.                                |
| beebmx.kirby-middleware.routes       |   []    | `array` | Customize your group `routes`.                                    |
| beebmx.kirby-middleware.web          |   []    | `array` | Add your own `web` middlewares.                                   |
| beebmx.kirby-middleware.auth         |   []    | `array` | Add your own `auth` middlewares.                                  |
| beebmx.kirby-middleware.guest        |   []    | `array` | Add your own `guest` middlewares.                                 |
| beebmx.kirby-middleware.redirections |   []    | `array` | Customize your `redirections` for `auth` and `guest` middlewares. |

### Disable middleware

You can completly disable all middleware validations updating the `enable` value in the `config.php` file:

```php
'beebmx.kirby-middleware' => [
    'enabled' => false,
],
```

## Facades

There are some `facades` to simplify the use of this plugin:

| Facade                                    | Class                             | Instance of            |
|:------------------------------------------|:----------------------------------|:-----------------------|
| Beebmx\KirbyMiddleware\Facades\Middleware | Beebmx\KirbyMiddleware\Middleware | Middleware::instance() |
| Beebmx\KirbyMiddleware\Facades\Pipeline   | Beebmx\Pipeline\Pipeline          | new Pipeline           |
| Beebmx\KirbyMiddleware\Facades\Request    | Beebmx\KirbyMiddleware\Request    | Request::instance()    |

## Plugins

If you are creating your own plugin, and it's required to use some type of request manipulation, `Kirby Middleware` is also for you.

### Installation

First, you need to inform `Kirby Middleware` than you have some `global` middleware or `group` middleware to register.

The easyest way to do this, is with a hook

```php
use Kirby\Cms\App as Kirby;
use Beebmx\KirbyMiddleware\Facades\Middleware;

Kirby::plugin('beebmx/kirby-security', [
    'hooks' => [
        'system.loadPlugins:after' => function () {
            Middleware::appendToGroup('security', [
                ValidateUser::class,
                ValidateUserRole::class,
                ValidateUserTeam::class,
            ]);
        },
    ],
]);
```

### Global methods

You can add your own validations to the `global` middleware. To achieve this, you have several methods.

#### Append

The `append` method adds the middleware to the end of the `global` middleware.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::append(ValidateVisitor::class);
```

#### Prepend

The `prepend` method adds the middleware to the beginning of the `global` middleware.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::prepend(ValidateVisitor::class);
```

#### getGlobalMiddleware

The `getGlobalMiddleware` method returns an array of all the `global` middleware registered.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::getGlobalMiddleware();
```

### Group methods

You can add your own validations to the `groups` middleware. To achieve this, you have several methods.

#### Append

The `appendToGroup` method adds the middleware to the end of the `groups` middlewares.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::appendToGroup('security', [
    ValidateUser::class,
    ValidateUserRole::class,
    ValidateUserTeam::class,
]);
```

#### prependToGroup

The `prependToGroup` method adds the middleware to the beginning of the `groups` middlewares.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::prependToGroup('security', [
    ValidateUser::class,
    ValidateUserRole::class,
    ValidateUserTeam::class,
]);
```

#### removeFromGroup

The `removeFromGroup` method removes some middleware from a specific `group` middleware.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::removeFromGroup('security', ValidateVisitor::class);
```

#### addClassToGroup

The `addClassToGroup` method adds a `Middleware Group` class to the `groups` middlewares.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::addClassToGroup(SecurityMiddlewareGroup::class);
```

#### getMiddlewareGroups

The `getMiddlewareGroups` method returns an array of all the `groups` middleware registered.

```php
use Beebmx\KirbyMiddleware\Facades\Middleware;

Middleware::getMiddlewareGroups();
```

### Authenticate middleware

You can customize the `Authenticate` middleware without using options, but hook `system.loadPlugins:after`.

#### redirectUsing

To set the route to redirect if the user is not authenticated.

```php
use Beebmx\KirbyMiddleware\Middlewares\Authenticate;

Authenticate::redirectUsing('login');
```

#### setRoutes

If you want to set the routes for the `AuthMiddlewareGroup`.

```php
use Beebmx\KirbyMiddleware\MiddlewareGroups\AuthMiddlewareGroup;

AuthMiddlewareGroup::setRoutes([
    'dashboard',
    'logout',
]);
```

### RedirectIfAuthenticated middleware

You can customize the `RedirectIfAuthenticated` middleware without using options, but hook `system.loadPlugins:after`.

#### redirectUsing

To set the route to redirect if the user is authenticated.

```php
use Beebmx\KirbyMiddleware\Middlewares\RedirectIfAuthenticated;

RedirectIfAuthenticated::redirectUsing('dashboard');
```

#### setRoutes

If you want to set the routes for the `GuestMiddlewareGroup`.

```php
use Beebmx\KirbyMiddleware\MiddlewareGroups\GuestMiddlewareGroup;

GuestMiddlewareGroup::MiddlewareGroup::setRoutes([
    'login',
]);
```

> [!IMPORTANT]
> Remember, all the group middleware classes should extend `Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup` class.

## Roadmap

- [ ] Custom hooks
- [ ] More `global` middlewares by default
- [ ] More `web` middlewares by default
- [x] An `auth` middleware group.
- [x] A `guest` middleware group.

## License

Licensed under the [MIT](LICENSE.md).

## Credits

- Fernando Gutierrez [@beebmx](https://github.com/beebmx)
- [All Contributors](../../contributors)
