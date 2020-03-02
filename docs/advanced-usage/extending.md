---
title: Extending
weight: 4
---

## Extending User Models
Laravel's authorization features are available in models which implement the `Illuminate\Foundation\Auth\Access\Authorizable` trait. By default Laravel does this in `\App\User` by extending `Illuminate\Foundation\Auth\User`, in which the trait and `Illuminate\Contracts\Auth\Access\Authorizable` contract are declared.

If you are creating your own User models and wish Authorization features to be available, you need to implement `Illuminate\Contracts\Auth\Access\Authorizable` in one of those ways as well.


## Extending Role and Permission Models
If you are extending or replacing the role/permission models, you will need to specify your new models in this package's `config/permission.php` file. 

First be sure that you've published the configuration file (see the Installation instructions), and edit it to update the `models.role` and `models.permission` values to point to your new models.

Note the following requirements when extending/replacing the models: 


### Extending
If you need to EXTEND the existing `Role` or `Permission` models note that:

- Your `Role` model needs to extend the `Spatie\Permission\Models\Role` model
- Your `Permission` model needs to extend the `Spatie\Permission\Models\Permission` model

### Replacing
If you need to REPLACE the existing `Role` or `Permission` models you need to keep the following things in mind:

- Your `Role` model needs to implement the `Spatie\Permission\Contracts\Role` contract
- Your `Permission` model needs to implement the `Spatie\Permission\Contracts\Permission` contract

## Migrations - Adding fields to your models
You can add your own migrations to make changes to the role/permission tables, as you would for adding/changing fields in any other tables in your Laravel project.
Following that, you can add any necessary logic for interacting with those fields ... to your custom/extended Models.
