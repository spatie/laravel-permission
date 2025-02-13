---
title: Cache
weight: 5
---

Role and Permission data are cached to speed up performance.

## Automatic Cache Refresh Using Built-In Functions

When you **use the built-in functions** for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record:

```php
// When handling permissions assigned to roles:
$role->givePermissionTo('edit articles');
$role->revokePermissionTo('edit articles');
$role->syncPermissions(params);

// When linking roles to permissions:
$permission->assignRole('writer');
$permission->removeRole('writer');
$permission->syncRoles(params);
```

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then you will not see the changes reflected in the application unless you manually reset the cache.

Additionally, because the Role and Permission models are Eloquent models which implement the `RefreshesPermissionCache` trait, creating and deleting Roles and Permissions will automatically clear the cache. If you have created your own models which do not extend the default models then you will need to implement the trait yourself.

**NOTE: User-specific role/permission assignments are kept in-memory since v4.4.0, so the cache-reset is no longer called since v5.1.0 when updating User-related assignments.**
Examples:
```php
// These operations on a User do not call a cache-reset, because the User-related assignments are in-memory.
$user->assignRole('writer');
$user->removeRole('writer');
$user->syncRoles(params);
```

## Manual cache reset
To manually reset the cache for this package, you can run the following in your app code:
```php
app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
```

Or you can use an Artisan command:
```bash
php artisan permission:cache-reset
```
(This command is effectively an alias for `artisan cache:forget spatie.permission.cache` but respects the package config as well.)

## Cache Configuration Settings

This package allows you to customize cache-related operations via its config file. In most cases the defaults are fine; however, in a multitenancy situation you may wish to do some cache-prefix overrides when switching tenants. See below for more details.

### Cache Expiration Time

The default cache `expiration_time` is `24 hours`.
If you wish to alter the expiration time you may do so in the `config/permission.php` file, in the `cache` array.


### Cache Key

The default cache key is `spatie.permission.cache`.
We recommend not changing the cache "key" name. Usually changing it is a bad idea. More likely setting the cache `prefix` is better, as mentioned below.


### Cache Identifier / Prefix

Laravel Tip: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites running on your server, you could run into cache clashes between apps. 

To prevent other applications from accidentally using/changing your cached data, it is prudent to set your own cache `prefix` in Laravel's `/config/cache.php` to something unique for each application which shares the same caching service.

Most multi-tenant "packages" take care of this for you when switching tenants. Optionally you might need to change cache boot order by writing a custom [cache boostrapper](https://github.com/spatie/laravel-permission/discussions/2310#discussioncomment-10855389).

Tip: Most parts of your multitenancy app will relate to a single tenant during a given request lifecycle, so the following step will not be needed: However, in the less-common situation where your app might be switching between multiple tenants during a single request lifecycle (specifically: where changing the cache key/prefix (such as when switching between tenants) or switching the cache store), then after switching tenants or changing the cache configuration you will need to reinitialize the cache of the `PermissionRegistrar` so that the updated `CacheStore` and cache configuration are used.

```php
app()->make(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();
```


### Custom Cache Store

You can configure the package to use any of the Cache Stores you've configured in Laravel's `config/cache.php`. This way you can point this package's caching to its own specified resource.

In `config/permission.php` set `cache.store` to the name of any one of the `config/cache.php` stores you've defined.

## Disabling Cache

Setting `'cache.store' => 'array'` in `config/permission.php` will effectively disable caching by this package between requests (it will only cache in-memory until the current request is completed processing, never persisting it).

Alternatively, in development mode you can bypass ALL of Laravel's caching between visits by setting `CACHE_DRIVER=array` in `.env`. You can see an example of this in the default `phpunit.xml` file that comes with a new Laravel install. Of course, don't do this in production though!


## File cache Store

This situation is not specific to this package, but is mentioned here due to the common question being asked.

If you are using the `File` cache Store and run into problems clearing the cache, it is most likely because your filesystem's permissions are preventing the PHP CLI from altering the cache files because the PHP-FPM process is running as a different user. 

Work with your server administrator to fix filesystem ownership on your cache files.

## Database cache Store

TIP: If you have `CACHE_STORE=database` set in your `.env`, remember that [you must install Laravel's cache tables via a migration before performing any cache operations](https://laravel.com/docs/cache#prerequisites-database). If you fail to install those migrations, you'll run into errors like `Call to a member function perform() on null` when the cache store attempts to purge or update the cache. This package does strategic cache resets in various places, so may trigger that error if your app's cache dependencies aren't set up.

