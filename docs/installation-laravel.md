---
title: Installation in Laravel
weight: 4
---

This package can be used with Laravel 5.8 or higher.

1. Consult the Prerequisites page for important considerations regarding your User models!

2. This package publishes a `config/permission.php` file. If you already have a file by that name, you must rename or remove it.

3. You can install the package via composer:

        composer require spatie/laravel-permission

4. Optional: The service provider will automatically get registered. Or you may manually add the service provider in your `config/app.php` file:

    ```
    'providers' => [
        // ...
        Spatie\Permission\PermissionServiceProvider::class,
    ];
    ```

5. You should publish [the migration](https://github.com/spatie/laravel-permission/blob/master/database/migrations/create_permission_tables.php.stub) and the [`config/permission.php` config file](https://github.com/spatie/laravel-permission/blob/master/config/permission.php) with:

    ```
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    ```

6. NOTE: If you are using UUIDs, see the Advanced section of the docs on UUID steps, before you continue. It explains some changes you may want to make to the migrations and config file before continuing. It also mentions important considerations after extending this package's models for UUID capability.

7. Run the migrations: After the config and migration have been published and configured, you can create the tables for this package by running:

        php artisan migrate

8. Add the necessary trait to your User model: Consult the Basic Usage section of the docs for how to get started using the features of this package.


### Default config file contents

You can view the default config file contents at:

https://github.com/spatie/laravel-permission/blob/master/config/permission.php
