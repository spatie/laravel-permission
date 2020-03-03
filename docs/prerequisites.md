---
title: Prerequisites
weight: 3
---

This package can be used in Laravel 5.8 or higher.

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

Additionally, your `User` model/object MUST NOT have a `role` or `roles` property (or field in the database), nor a `roles()` method on it. Those will interfere with the properties and methods added by the `HasRoles` trait provided by this package, thus causing unexpected outcomes when this package's methods are used to inspect roles and permissions.

Similarly, your `User` model/object MUST NOT have a `permission` or `permissions` property (or field in the database), nor a `permissions()` method on it. Those will interfere with the properties and methods added by the `HasPermissions` trait provided by this package (which is invoked via the `HasRoles` trait).

This package publishes a `config/permission.php` file. If you already have a file by that name, you must rename or remove it, as it will conflict with this package. You could optionally merge your own values with those required by this package, as long as the keys that this package expects are present. See the source file for more details.

