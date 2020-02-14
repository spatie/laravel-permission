---
title: Model Policies
weight: 2
---

The best way to incorporate access control for application features is with [Laravel's Model Policies](https://laravel.com/docs/authorization#creating-policies).

Using Policies allows you to simplify things by abstracting your "control" rules into one place, where your application logic can be combined with your permission rules.

You can find an example of implementing a model policy with this Laravel Permissions package in this demo app: https://github.com/drbyte/spatie-permissions-demo/blob/master/app/Policies/PostPolicy.php
