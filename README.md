# Laravel AB by Pivotal

## Currently under testing for official release - 4.4.2024

![example workflow](https://github.com/pivotalso/laravel-ab/actions/workflows/tests.yml/badge.svg)

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

If you want to send your events to Pivotal AB, you must configure the library to listen for save events.
Add the following to your `EventServiceProvider`:


```php
  ....
  use pivotalso\LaravelAb\Events\Track;
  use pivotalso\LaravelAb\Listeners\TrackerLogger;
  
  class EventServiceProvider extends ServiceProvider {
       protected $listen = [
        ...,
        Track::class => [
            TrackerLogger::class,
        ],
    ];
```
as well as add `LARAVEL_AB_API_KEY` to your `.env` file
You can get your api key from your project settings page on Pivotal AB.


```bash
You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="ab-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="ab-config"

/// ab.php
return [
    'cache_key' => 'laravel_ab_user',
    'request_param'=> env('LARAVEL_AB_REQUEST_PARAM', 'abid'), /// listen for query string param to override instance id
    'allow_param'=> env('LARAVEL_AB_ALLOW_PARAM', false), /// allows for the use of request param
    'api_key' => env('LARAVEL_AB_API_KEY', ''), // the api key for pivotal intelligence
];

```


## Documentation
You can find the documentation for this package at [https://docs.pivotal.so/docs/ab/laravel](https://docs.pivotal.so/ab)

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
You can also easily test logic within you Controllers
```bash 
class PagesController extends Controller
{
    public function welcome()
    {
       $option =  
       Ab::choice('kind of homepage', ['control', 'variant'])->track('go-to-ab');
       if ($option === 'variant') {
           return view('variant-welcome');
        }
       return view('welcome');
    }
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
a sample output would be
```bash

{
    "hero-text": [
        {
            "condition": "my website",
            "hits": 6,
            "goals": 3,
            "conversion": 50
        },
        {
            "condition": "welcome user",
            "hits": 12,
            "goals": 3,
            "conversion": 25
        }
    ]
}
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
