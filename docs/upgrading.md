---
title: Upgrading
weight: 6
---

## Upgrade Essentials

ALL upgrades of this package should follow these steps:

1. Upgrading between major versions of this package always require the usual Composer steps:
   - Update your `composer.json` to specify the new major version, such as `^5.0`
   - Then run `composer update`. 

2. Compare the `migration` file stubs in the NEW version of this package against the migrations you've already run inside your app. If necessary, create a new migration (by hand) to apply any new changes.

3. If you have made any custom Models from this package into your own app, compare the old and new models and apply any relevant updates to your custom models.

4. If you have overridden any methods from this package's Traits, compare the old and new traits, and apply any relevant updates to your overridden methods.

5. Apply any version-specific special updates as outlined below...


## Upgrading from v1 to v2
If you're upgrading from v1 to v2, there's no built-in automatic migration/conversion of your data to the new structure. 
You will need to carefully adapt your code and your data manually.

Tip: @fabricecw prepared [a gist which may make your data migration easier](https://gist.github.com/fabricecw/58ee93dd4f99e78724d8acbb851658a4). 

You will also need to remove your old `laravel-permission.php` config file and publish the new one `permission.php`, and edit accordingly (setting up your custom settings again in the new file, where relevant).
