---
title: Roles vs Permissions
weight: 1
---

Best-Practice for thinking about Roles vs Permissions is this:

**Roles** are best to only assign to **Users** in order to "**group**" people by "**sets of permissions**".

**Permissions** are best assigned **to roles**. 
The more granular/detailed your permission-names (such as separate permissions like "view document" and "edit document"), the easier it is to control access in your application.

**Users** should *rarely* be given "direct" permissions. Best if Users inherit permissions via the Roles that they're assigned to.

When designed this way, all the sections of your application can check for specific permissions needed to access certain features or perform certain actions AND this way you can always **use the native Laravel `@can` and `can()` directives everywhere** in your app, which allows Laravel's Gate layer to do all the heavy lifting.  

Example: it's safer to have your Views test `@can('view member addresses')` or `@can('edit document')`, INSTEAD of testing for `$user->hasRole('Editor')`. It's easier to control displaying a "section" of content vs edit/delete buttons if you have "view document" and "edit document" permissions defined. And then Writer role would get both "view" and "edit" assigned to it. And then the user would get the Writer role.

This also allows you to treat permission names as static (only editable by developers), and then your application (almost) never needs to know anything about role names, so you could (almost) change role names at will.

Summary:
- **users** have `roles`
- **roles** have `permissions`
- app always checks for `permissions` (as much as possible), not `roles`
- **views** check permission-names
- **policies** check permission-names
- **model policies** check permission-names
- **controller methods** check permission-names
- **middleware** check permission names, or sometimes role-names
- **routes** check permission-names, or maybe role-names if you need to code that way.

Sometimes certain groups of `route` rules may make best sense to group them around a `role`, but still, whenever possible, there is less overhead used if you can check against a specific `permission` instead.


### FURTHER READING:

[@joelclermont](https://github.com/joelclermont) at [masteringlaravel.io](https://masteringlaravel.io/daily) offers similar guidance in his post about [Treating Feature Access As Data, Not Code](https://masteringlaravel.io/daily/2025-01-09-treat-feature-access-as-data-not-code)
