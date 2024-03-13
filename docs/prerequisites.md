---
title: Prerequisites
weight: 3
---

## Laravel Version

This package can be used in Laravel 6 or higher. Check the "Installing on Laravel" page for package versions compatible with various Laravel versions.

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

## Must not have a [role] or [roles] property, nor a [roles()] method

Your `User` model/object MUST NOT have a `role` or `roles` property (or field in the database by that name), nor a `roles()` method on it. Those will interfere with the properties and methods added by the `HasRoles` trait provided by this package, thus causing unexpected outcomes when this package's methods are used to inspect roles and permissions.

## Must not have a [permission] or [permissions] property, nor a [permissions()] method

Your `User` model/object MUST NOT have a `permission` or `permissions` property (or field in the database by that name), nor a `permissions()` method on it. Those will interfere with the properties and methods added by the `HasPermissions` trait provided by this package (which is invoked via the `HasRoles` trait).

## Config file

This package publishes a `config/permission.php` file. If you already have a file by that name, you must rename or remove it, as it will conflict with this package. You could optionally merge your own values with those required by this package, as long as the keys that this package expects are present. See the source file for more details.

## Schema Limitation in MySQL

MySQL 8.0 limits index keys to 1000 characters. This package publishes a migration which combines multiple columns in single index. With `utf8mb4` the 4-bytes-per-character requirement of `mb4` means the max length of the columns in the hybrid index can only be `125` characters.

Thus in your AppServiceProvider you will need to set `Schema::defaultStringLength(125)`. [See the Laravel Docs for instructions](https://laravel.com/docs/migrations#index-lengths-mysql-mariadb).

You may be able to bypass setting `defaultStringLength(125)` by editing the migration and specifying the `125` in 4 fields. There are 2 instances of this code snippet where you can explicitly set the `125`:
```php
    $table->string('name');       // For MySQL 8.0 use string('name', 125);
    $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);
```

## Note for apps using UUIDs/ULIDs/GUIDs

This package expects the primary key of your `User` model to be an auto-incrementing `int`. If it is not, you may need to modify the `create_permission_tables` migration and/or modify the default configuration. See [https://spatie.be/docs/laravel-permission/advanced-usage/uuid](https://spatie.be/docs/laravel-permission/advanced-usage/uuid) for more information. 

## Database foreign-key relationship support

To enforce database integrity, this package uses foreign-key relationships with cascading deletes. This prevents data mismatch situations if database records are manipulated outside of this package. If your database engine does not support foreign-key relationships, then you will have to alter the migration files accordingly.

This package does its own detaching of pivot records when deletes are called using provided package methods, so if your database does not support foreign keys then as long as you only use method calls provided by this package for managing related records, there should not be data integrity issues.
