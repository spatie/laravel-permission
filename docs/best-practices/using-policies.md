---
title: Model Policies
weight: 2
---

The best way to incorporate access control for application features is with [Laravel's Model Policies](https://laravel.com/docs/authorization#creating-policies).

Using Policies allows you to simplify things by abstracting your "control" rules into one place, where your application logic can be combined with your permission rules.

Jeffrey Way explains the concept simply in the [Laravel 6 Authorization Filters](https://laracasts.com/series/laravel-6-from-scratch/episodes/51) and [policies](https://laracasts.com/series/laravel-6-from-scratch/episodes/63) videos and in other related lessons in that chapter. He also mentions how to set up a super-admin, both in a model policy and globally in your application.

You can find an example of implementing a model policy with this Laravel Permissions package in this demo app: https://github.com/drbyte/spatie-permissions-demo/blob/master/app/Policies/PostPolicy.php
