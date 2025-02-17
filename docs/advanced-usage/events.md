---
title: Events
weight: 5
---

By default Events are not enabled, because not all apps need to fire events related to roles and permissions.

However, you may enable events by setting the `events_enabled => true` in `config/permission.php`

## Available Events

The following events are available since `v6.15.0`:

```
\Spatie\Permission\Events\RoleAttached::class
\Spatie\Permission\Events\RoleDetached::class
\Spatie\Permission\Events\PermissionAttached::class
\Spatie\Permission\Events\PermissionDetached::class
```
Note that the events can receive the role or permission details as a model ID or as an Eloquent record, or as an array or collection of ids or records. Be sure to inspect the parameter before acting on it.

