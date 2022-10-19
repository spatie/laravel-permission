---
title: Roles vs Permissions
weight: 1
---

It is generally best to code your app around testing against `permissions` only. (ie: when testing whether to grant access to something, in most cases it's wisest to check against a `permission`, not a `role`). That way you can always use the native Laravel `@can` and `can()` directives everywhere in your app.

Roles can still be used to group permissions for easy assignment to a user/model, and you can still use the role-based helper methods if truly necessary. But most app-related logic can usually be best controlled using the `can` methods, which allows Laravel's Gate layer to do all the heavy lifting.  Sometimes certain groups of `route` rules may make best sense to group them around a `role`, but still, whenever possible, there is less overhead used if you can check against a specific `permission` instead.

eg: `users` have `roles`, and `roles` have `permissions`, and your app always checks for `permissions`, not `roles`.

