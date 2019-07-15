---
title: Defining a Super-Admin
weight: 5
---

We strongly recommend that a Super-Admin be handled by setting a global `Gate::before` or `Gate::after` rule which checks for the desired role. 

Then you can implement the best-practice of primarily using permission-based controls throughout your app, without always having to check for "is this a super-admin" everywhere.

See this wiki article on [Defining a Super-Admin Gate rule](https://github.com/spatie/laravel-permission/wiki/Global-%22Admin%22-role) in your app.
