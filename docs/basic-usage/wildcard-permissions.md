---
title: Wildcard permissions
weight: 6
---

When enabled, wildcard permissions offers you a flexible representation for a variety of permission schemes. 

The wildcard permissions implementation is inspired by the default permission implementation of 
 [Apache Shiro](https://shiro.apache.org/permissions.html). See the Shiro documentation for more examples and deeper explanation of the concepts.

## Enabling Wildcard Features

Wildcard permissions can be enabled in the permission config file:

```php
// config/permission.php
'enable_wildcard_permission' => true,
```

## Wildcard Syntax

A wildcard permission string is made of one or more parts separated by dots (.).

```php
$permission = 'posts.create.1';
```

The meaning of each part of the string depends on the application layer. 

> You can use as many parts as you like. So you are not limited to the three-tiered structure, even though 
this is the common use-case, representing `{resource}.{action}.{target}`.

> **NOTE: You must actually create the wildcarded permissions** (eg: `posts.create.1`) before you can assign them or check for them.

> **NOTE: You must create any wildcard permission patterns** (eg: `posts.create.*`) before you can assign them or check for them.

## Using Wildcards

> ALERT: The `*` means "ALL". It does **not** mean "ANY".

Each part can also contain wildcards (`*`). So let's say we assign the following permission to a user:

```php
Permission::create(['name'=>'posts.*']);
$user->givePermissionTo('posts.*');
// is the same as
Permission::create(['name'=>'posts']);
$user->givePermissionTo('posts');
```

Given the example above, everyone who is assigned to this permission will be allowed every action on posts. It is not necessary to use a 
wildcard on the last part of the string. This is automatically assumed.

```php
// will be true
$user->can('posts.create');
$user->can('posts.edit');
$user->can('posts.delete');
```
(Note that the `posts.create` and `posts.edit` and `posts.delete` permissions must also be created.)

## Meaning of the * Asterisk

The `*` means "ALL". It does **not** mean "ANY".

Thus `can('post.*')` will only pass if the user has been assigned `post.*` explicitly, and the `post.*` Permission has been created.


## Subparts

Besides the use of parts and wildcards, subparts can also be used. Subparts are divided with commas (,). This is a 
powerful feature that lets you create complex permission schemes.

```php
// user can only do the actions create, update and view on both resources posts and users
Permission::create(['name'=>'posts,users.create,update,view']);
$user->givePermissionTo('posts,users.create,update,view');

// user can do the actions create, update, view on any available resource
Permission::create(['name'=>'*.create,update,view']);
$user->givePermissionTo('*.create,update,view');

// user can do any action on posts with ids 1, 4 and 6 
Permission::create(['name'=>'posts.*.1,4,6']);
$user->givePermissionTo('posts.*.1,4,6');
```

> Remember: the meaning of each 'part' is determined by your application! So, you are free to use each part as you like. And you can use as many parts and subparts as you want.
