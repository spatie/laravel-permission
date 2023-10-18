---
title: Passport Client Credentials Grant usage
weight: 12
---

**NOTE** currently this only works for Laravel 9 and Passport 11 and newer.

## Install Passport
First of all make sure to have Passport installed as described in the [Laravel documentation](https://laravel.com/docs/master/passport).

## Extend the Client model
After installing the Passport package we need to extend Passports Client model. 
The extended Client model should look like something as shown below.

```php
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Passport\Client as BaseClient;
use Spatie\Permission\Traits\HasRoles;

class Client extends BaseClient implements AuthorizableContract
{
    use HasRoles;
    use Authorizable;

    public $guard_name = 'api';
    
    // or
    
    public function guardName()
    {
        return 'api'
    }
}
```

You need to extend the Client model to make it possible to add the required traits and properties/ methods.
The extended Client should either provide a `$guard_name` property or a `guardName()` method.
They should return a string that matches the [configured](https://laravel.com/docs/master/passport#installation) guard name for the passport driver.

To tell Passport to use this extended Client, add the rule below to the `boot` method of your `App\Providers\AuthServiceProvider` class.
```php
Passport::useClientModel(\App\Models\Client::class); // Use the namespace of your extended Client.
```

## Middleware
All middleware provided by this package work with the Client.

Do make sure that you only wrap your routes in the [`client`](https://laravel.com/docs/master/passport#via-middleware) middleware and not the `auth:api` middleware as well.
Wrapping routes in the `auth:api` middleware currently does not work for the Client Credentials Grant.

## Config
Finally, update the config file as well. Setting `use_passport_client_credentials` to `true` will make sure that the right checks are performed.

```php
// config/permission.php
'use_passport_client_credentials' => true,
```
