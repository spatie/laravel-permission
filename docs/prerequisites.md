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

Potential error message: "1071 Specified key was too long; max key length is 1000 bytes"

MySQL 8.0 limits index key lengths, which might be too short for some compound indexes used by this package.
This package publishes a migration which combines multiple columns in a single index. With `utf8mb4` the 4-bytes-per-character requirement of `mb4` means the total length of the columns in the hybrid index can only be `25%` of that maximum index length.

- MyISAM tables limit the index to 1000 characters (which is only 250 total chars in `utf8mb4`)
- InnoDB tables using ROW_FORMAT of 'Redundant' or 'Compact' limit the index to 767 characters (which is only 191 total chars in `utf8mb4`)
- InnoDB tables using ROW_FORMAT of 'Dynamic' or 'Compressed' have a 3072 character limit (which is 768 total chars in `utf8mb4`).

Depending on your MySQL or MariaDB configuration, you may implement one of the following approaches:

1. Ideally, configure the database to use InnoDB by default, and use ROW FORMAT of 'Dynamic' by default for all new tables. (See [MySQL](https://dev.mysql.com/doc/refman/8.0/en/innodb-limits.html) and [MariaDB](https://mariadb.com/kb/en/innodb-dynamic-row-format/) docs.)

2. OR if your app doesn't require a longer default, in your AppServiceProvider you can set `Schema::defaultStringLength(125)`. [See the Laravel Docs for instructions](https://laravel.com/docs/migrations#index-lengths-mysql-mariadb). This will have Laravel set all strings to 125 characters by default.

3. OR you could edit the migration and specify a shorter length for 4 fields. Then in your app be sure to manually impose validation limits on any form fields related to these fields. 
There are 2 instances of this code snippet where you can explicitly set the length.:
```php
    $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
    $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
```

## Note for apps using UUIDs/ULIDs/GUIDs

This package expects the primary key of your `User` model to be an auto-incrementing `int`. If it is not, you may need to modify the `create_permission_tables` migration and/or modify the default configuration. See [https://spatie.be/docs/laravel-permission/advanced-usage/uuid](https://spatie.be/docs/laravel-permission/advanced-usage/uuid) for more information. 

## Database foreign-key relationship support

To enforce database integrity, this package uses foreign-key relationships with cascading deletes. This prevents data mismatch situations if database records are manipulated outside of this package. If your database engine does not support foreign-key relationships, then you will have to alter the migration files accordingly.

This package does its own detaching of pivot records when deletes are called using provided package methods, so if your database does not support foreign keys then as long as you only use method calls provided by this package for managing related records, there should not be data integrity issues.
