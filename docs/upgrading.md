---
title: Upgrading
weight: 6
---

### Upgrading from v2 to v3
There are no special requirements for upgrading from v2 to v3, other than changing `^2.xx` (xx can vary) to `^3.0` in your `composer.json` and running `composer update`. Of course, your app must meet the minimum requirements as well.

### Upgrading from v1 to v2
If you're upgrading from v1 to v2, there's no built-in automatic migration/conversion of your data. 
You will need to carefully adapt your code and your data manually.

Tip: @fabricecw prepared [a gist which may make your data migration easier](https://gist.github.com/fabricecw/58ee93dd4f99e78724d8acbb851658a4). 

You will also need to remove your old `laravel-permission.php` config file and publish the new one `permission.php`, and edit accordingly (setting up your custom settings again in the new file, where relevant).
