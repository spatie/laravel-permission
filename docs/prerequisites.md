---
title: Prerequisites
weight: 3
---

## Laravel Version

This package can be used in Laravel 6 or higher.

## User Model / Contract/Interface

This package uses Laravel's Gate layer to provide Authorization capabilities.
The Gate/authorization layer requires that your `User` model implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract. 
Otherwise the `can()` and `authorize()` methods will not work in your controllers, policies, templates, etc.

In the `Installation` instructions you'll see that the `HasRoles` trait must be added to the User model to enable this package's features.

Thus, a typical basic User model would have these basic minimum requirements:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}
```

## Must not have a `role` or `roles` property, nor a `roles()` method

Additionally, your `User` model/object MUST NOT have a `role` or `roles` property (or field in the database), nor a `roles()` method on it. Those will interfere with the properties and methods added by the `HasRoles` trait provided by this package, thus causing unexpected outcomes when this package's methods are used to inspect roles and permissions.

## Must not have a `permission` or `permissions` property, nor a `permissions()` method

Similarly, your `User` model/object MUST NOT have a `permission` or `permissions` property (or field in the database), nor a `permissions()` method on it. Those will interfere with the properties and methods added by the `HasPermissions` trait provided by this package (which is invoked via the `HasRoles` trait).

## Config file

This package publishes a `config/permission.php` file. If you already have a file by that name, you must rename or remove it, as it will conflict with this package. You could optionally merge your own values with those required by this package, as long as the keys that this package expects are present. See the source file for more details.

## Schema Limitation in MySQL

MySQL 8.0 limits index keys to 1000 characters. This package publishes a migration which combines multiple columns in single index. With `utf8mb4` the 4-bytes-per-character requirement of `mb4` means the max length of the columns in the hybrid index can only be `125` characters.

Thus in your AppServiceProvider you will need to set `Schema::defaultStringLength(125)`. [See the Laravel Docs for instructions](https://laravel.com/docs/master/migrations#index-lengths-mysql-mariadb).

