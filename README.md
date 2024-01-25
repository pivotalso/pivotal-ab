# Laravel AB by Pivotal (WIP)
NOTE:: This package is still in development and not ready for production use.

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/82rules/laravel-ab/run-tests?label=tests&style=flat-square)](

[![Latest Version on Packagist](https://img.shields.io/packagist/v/82rules/laravel-ab.svg?style=flat-square)](https://packagist.org/packages/82rules/laravel-ab)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/82rules/laravel-ab/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/82rules/laravel-ab/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/82rules/laravel-ab/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/82rules/laravel-ab/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/82rules/laravel-ab.svg?style=flat-square)](https://packagist.org/packages/82rules/laravel-ab)

Laravel A/B by Pivotal is a package to help you create and manage A/B tests 
on your laravel blade templates. It also provides a way to generate and view reports 
locally or by integration into Pivotal AB services.

## Installation

You can install the package via composer:

```bash
composer require pivotalso/laravel-ab
```
Add the service provider in `config/app.php`:

```php
    'providers' => ServiceProvider::defaultProviders()->merge([
        ...
        pivotalso\LaravelAb\LaravelAbServiceProvider::class,
    ]),
````

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="ab-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ab-config"
```

## Usage
Here is an example use case of a nested A/B test tracking signup and free trial goals

``` welcome.blade.php```
``` 
<html>
    <head>
        <title>My Website</title>
    </head>
    <body>
        @ab('hero-text')
        @condtion('my website')
            <h1>My Website</h1>
        @condition('welcome user')
            <h1>Welcome, {{ $user->name }}</h1> 
                  @ab('free offer for new users')
                    @condtion('free trial')
                        <button>Start your free trial</button>
                    @condition('get started')
                        <button>Get Started</button>      
                    @track('free trial')       
        @track('sign up')
    </body>
</html
```

You can either track goals in views or within your application logic.
``` register.blade.php```
``` 
<html>
    <head>
        <title>My Website</title>
    </head>
    <body>
        .... body
        @goal('sign up')
    </body>
</html
```
or for example
```RegistrationController.php```
```
    public function store(Request $request)
    {
        .... store logic
        LaravelAb::goal('sign up');
        if($request->has('free_trial')) {
            LaravelAb::goal('free trial');
        }
    }
```

## Reporting
You can view reports locally by running the following command
```bash
php artisan ab:report
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rulian Estivalletti](https://github.com/82rules)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
