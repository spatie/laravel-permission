---
title: Roles vs Permissions
weight: 1
---

It is generally best to code your app around `permissions` only. That way you can always use the native Laravel `@can` and `can()` directives everywhere in your app.

Roles can still be used to group permissions for easy assignment, and you can still use the role-based helper methods if truly necessary. But most app-related logic can usually be best controlled using the `can` methods, which allows Laravel's Gate layer to do all the heavy lifting.

eg: `users` have `roles`, and `roles` have `permissions`, and your app always checks for `permissions`, not `roles`.

