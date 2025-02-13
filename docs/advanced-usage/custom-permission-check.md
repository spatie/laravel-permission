---
title: Custom Permission Check
weight: 6
---

## Default Permission Check Functionality
By default, this package registers a `Gate::before()` method call on [Laravel's gate](https://laravel.com/docs/authorization). This method is responsible for checking if the user has the required permission or not, for calls to `can()` helpers and most `model policies`. Whether a user has a permission or not is determined by checking the user's permissions stored in the database.

In the permission config file, `register_permission_check_method` is set to `true`, which means this package operates using the default behavior described above. Only set this to `false` if you want to bypass the default operation and implement your own custom logic for checking permissions, as described below.

## Using Custom Permission Check Functionality

However, in some cases, you might want to implement custom logic for checking if the user has a permission or not.

Let's say that your application uses access tokens for authentication and when issuing the tokens, you add a custom claim containing all the permissions the user has. In this case, if you want to check whether the user has the required permission or not based on the permissions in your custom claim in the access token, then you need to implement your own logic for handling this.

You could, for example, create a `Gate::before()` method call to handle this:

**app/Providers/AuthServiceProvider.php** (or maybe `AppServiceProvider.php` since Laravel 11)
```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    ...

    Gate::before(function ($user, $ability) {
        return $user->hasTokenPermission($ability) ?: null;
    });
}
```
Here `hasTokenPermission` is a **custom method you need to implement yourself**.

