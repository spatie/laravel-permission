---
title: Cache
weight: 5
---

Role and Permission data are cached to speed up performance.

### Automatic Cache Refresh Using Built-In Functions

When you **use the built-in functions** for manipulating roles and permissions, the cache is automatically reset for you, and relations are automatically reloaded for the current model record:

```php
$user->assignRole('writer');
$user->removeRole('writer');
$user->syncRoles(params);
$role->givePermissionTo('edit articles');
$role->revokePermissionTo('edit articles');
$role->syncPermissions(params);
$permission->assignRole('writer');
$permission->removeRole('writer');
$permission->syncRoles(params);
```

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then you will not see the changes reflected in the application unless you manually reset the cache.

Additionally, because the Role and Permission models are Eloquent models which implement the `RefreshesPermissionCache` trait, creating and deleting Roles and Permissions will automatically clear the cache. If you have created your own models which do not extend the default models then you will need to implement the trait yourself.


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


