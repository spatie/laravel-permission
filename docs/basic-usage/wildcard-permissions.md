---
title: Wildcard permissions
weight: 3
---

Wildcard permissions can be enabled in the permission config file:

```php
// config/permission.php
'enable_wildcard_permission' => true,
```

When enabled, wildcard permissions offers you a flexible representation for a variety of permission schemes. The idea
 behind wildcard permissions is inspired by the default permission implementation of 
 [Apache Shiro](https://shiro.apache.org/permissions.html).

A wildcard permission string is made of one or more parts separated by dots (.).

```php
$permission = 'posts.create.1';
```

The meaning of each part of the string depends on the application layer. 

> You can use as many parts as you like. So you are not limited to the three-tiered structure, even though 
this is the common use-case, representing {resource}.{action}.{target}.

> NOTE: You must actually create the permissions (eg: `posts.create.1`) before you can assign them, and must also create any wildcard permission patterns (eg: `posts.create.*`) before you can check for them.

### Using Wildcards

Each part can also contain wildcards (*). So let's say we assign the following permission to a user:

```php
Permission::create(['name'=>'posts.*']);
$user->givePermissionTo('posts.*');
// is the same as
Permission::create(['name'=>'posts']);
$user->givePermissionTo('posts');
```

Everyone who is assigned to this permission will be allowed every action on posts. It is not necessary to use a 
wildcard on the last part of the string. This is automatically assumed.

```php
// will be true
$user->can('posts.create');
$user->can('posts.edit');
$user->can('posts.delete');
``` 

### Subparts

Besides the use of parts and wildcards, subparts can also be used. Subparts are divided with commas (,). This is a 
powerful feature that lets you create complex permission schemes.

```php
// user can only do the actions create, update and view on both resources posts and users
$user->givePermissionTo('posts,users.create,update,view');

// user can do the actions create, update, view on any available resource
$user->givePermissionTo('*.create,update,view');

// user can do any action on posts with ids 1, 4 and 6 
$user->givePermissionTo('posts.*.1,4,6');
```

> As said before, the meaning of each part is determined by the application layer! So, you are free to use each part as you like. And you can use as many parts and subparts as you want.
