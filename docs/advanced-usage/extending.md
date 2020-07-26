---
title: Extending
weight: 4
---

## Extending User Models
Laravel's authorization features are available in models which implement the `Illuminate\Foundation\Auth\Access\Authorizable` trait.

By default Laravel does this in `\App\User` by extending `Illuminate\Foundation\Auth\User`, in which the trait and `Illuminate\Contracts\Auth\Access\Authorizable` contract are declared.

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
Following that, you can add any necessary logic for interacting with those fields into your custom/extended Models.

## Artisan commands - Entering values for newly added columns in database table
By default, if you do not manually change the table structure of the Role and Permission models, the `php artisan permission:create-xxx` commands will be used as in the basic instructions (see [here](https://docs.spatie.be/laravel-permission/v3/basic-usage/artisan/)).

But conversely, if you do generate new columns in the database table, these commands will have new arguments and options depending on how the columns are set in the database table. Specifically, columns that are required to enter values ​​(not nullable) and without default values ​​will become command arguments, vice versa will become command options.

For example, if we add a column named `module` (string type, not nullable, no default value) and a column named `is_builtin` (tinyInt type, default value is 0) to the table of Permission model, then the `module` column will become command argument and `is_builtin` will become command option.

Then the command to create permission will be used as follows:

**Syntax helper**:

```
Description:
  Create a permission

Usage:
  permission:create-permission [options] [--] <name> <module>

Arguments:
  name                           The name of the permission
  module                         The module of the permission

Options:
      --guard[=GUARD]            The name of the guard [default: "web"]
      --is_builtin[=IS_BUILTIN]  The is_builtin status of the permission [default: "0"]
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
      --env[=ENV]                The environment the command should run under
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

**Examples**:

Create the permission with the name is `create articles` and the module is `Article`:

```bash
php artisan permission:create-permission "create articles" Article
```

Create the permission with the name is `create articles` and the module is `Article` for the specific guard:

```bash
php artisan permission:create-permission "create articles" Article --guard=another
```

Create the permission with the name is `create articles`, the module is `Article` and the is_builtin status is `1`:

```bash
php artisan permission:create-permission "create articles" Article --is_builtin=1
```

When creating roles and create and link permissions at the same time:

```bash
php artisan permission:create-role writer --permissions="name:create articles,module:Article|name:edit articles,module:Article"
```
