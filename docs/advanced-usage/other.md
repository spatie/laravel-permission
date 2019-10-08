---
title: Other
weight: 8
---

### Accessing pivot tables timestamps

The `role_has_permission`, `model_has_permissions` and `model_has_roles` pivot tables have timestamps columns that can be accessed if you need to. To access them, as described in the Laravel's [documentation about Eloquent relationships](https://laravel.com/docs/eloquent-relationships), you need to explicitly call the property through the `pivot` attribute.

Here's an example to get the `created_at` property for all model assigned permissions:

```php
foreach ($model->permissions as $permission) {
    echo $permission->pivot->created_at;
}
```
 
 The key part is the `pivot->created_at`, which will get the date and time of when the relationship was made.
