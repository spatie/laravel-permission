---
title: Cache
weight: 5
---

Role and Permission data are cached to speed up performance.

### Automatic Cache Refresh Using Built-In Functions

When you **use the built-in functions** for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record:

```php
$role->givePermissionTo('edit articles');
$role->revokePermissionTo('edit articles');
$role->syncPermissions(params);
$permission->assignRole('writer');
$permission->removeRole('writer');
$permission->syncRoles(params);
```

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then you will not see the changes reflected in the application unless you manually reset the cache.

Additionally, because the Role and Permission models are Eloquent models which implement the `RefreshesPermissionCache` trait, creating and deleting Roles and Permissions will automatically clear the cache. If you have created your own models which do not extend the default models then you will need to implement the trait yourself.

**NOTE: User-specific role/permission assignments are kept in-memory since v4.4.0, so the cache-reset is no longer called since v5.1.0 when updating User-related assignments.**
Examples:
```php
// These do not call a cache-reset, because the User-related assignments are in-memory.
$user->assignRole('writer');
$user->removeRole('writer');
$user->syncRoles(params);
```

### Manual cache reset
To manually reset the cache for this package, you can run the following in your app code:
```php
app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
```

Or you can use an Artisan command:
```bash
php artisan permission:cache-reset
```
(This command is effectively an alias for `artisan cache:forget spatie.permission.cache` but respects the package config as well.)


### Cache Expiration Time

The default cache `expiration_time` is `24 hours`.
If you wish to alter the expiration time you may do so in the `config/permission.php` file, in the `cache` array.


### Cache Key

The default cache key is `spatie.permission.cache`.
We recommend not changing the cache "key" name. Usually changing it is a bad idea. More likely setting the cache `prefix` is better, as mentioned above.


### Cache Identifier / Prefix

Laravel Tip: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites running on your server, you could run into cache clashes between apps. 

To prevent other applications from accidentally using/changing your cached data, it is prudent to set your own cache `prefix` in Laravel's `/config/cache.php` to something unique for each application which shares the same caching service.

Most multi-tenant "packages" take care of this for you when switching tenants.


### Custom Cache Store

You can configure the package to use any of the Cache Stores you've configured in Laravel's `config/cache.php`. This way you can point this package's caching to its own specified resource.

In `config/permission.php` set `cache.store` to the name of any one of the `config/cache.php` stores you've defined.

#### Disabling Cache

Setting `'cache.store' => 'array'` in `config/permission.php` will effectively disable caching by this package between requests (it will only cache in-memory until the current request is completed processing, never persisting it).

Alternatively, in development mode you can bypass ALL of Laravel's caching between visits by setting `CACHE_DRIVER=array` in `.env`. You can see an example of this in the default `phpunit.xml` file that comes with a new Laravel install. Of course, don't do this in production though!


## File cache driver

This situation is not specific to this package, but is mentioned here due to the common question being asked.

If you are using the `File` cache driver and run into problems clearing the cache, it is most likely because your filesystem's permissions are preventing the PHP CLI from altering the cache files because the PHP-FPM process is running as a different user. 

Work with your server administrator to fix filesystem ownership on your cache files.
