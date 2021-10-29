---
title: Custom Permission Check
weight: 6
---

By default, a method is registered on [Laravel's gate](https://laravel.com/docs/authorization). This method is responsible for checking if the user has the required permission or not. Whether a user has a permission or not is determined by checking the user's permissions stored in the database.

However, in some cases, you might want to implement custom logic for checking if the user has a permission or not.

Let's say that your application uses access tokens for authentication and when issuing the tokens, you add a custom claim containing all the permissions the user has. In this case, if you want to check whether the user has the required permission or not based on the permissions in your custom claim in the access token, then you need to implement your own logic for handling this.

You could, for example, create a `before` method to handle this:

**app/Providers/AuthServiceProvider.php**
```php
public function boot()
{
    ...

    Gate::before(function ($user, $ability) {
        return $user->hasTokenPermission($ability) ?: null;
    });
}
```
Here `hasTokenPermission` is a custom method you need to implement yourself.

### Register Permission Check Method
By default, `register_permission_check_method` is set to `true`.
Only set this to false if you want to implement custom logic for checking permissions.