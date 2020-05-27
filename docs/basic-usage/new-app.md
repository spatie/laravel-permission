---
title: Example App
weight: 90
---

## Creating A Demo App

If you want to just try out the features of this package you can get started with the following.

The examples on this page are primarily added for assistance in creating a quick demo app for troubleshooting purposes, to post the repo on github for convenient sharing to collaborate or get support.

If you're new to Laravel or to any of the concepts mentioned here, you can learn more in the [Laravel documentation](https://laravel.com/docs/) and in the free videos at Laracasts such as this series: https://laracasts.com/series/laravel-6-from-scratch/

### Initial setup:

```sh
cd ~/Sites
laravel new mypermissionsdemo
cd mypermissionsdemo
git init
git add .
git commit -m "Fresh Laravel Install"

# Environment
cp -n .env.example .env
sed -i '' 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i '' 's/DB_DATABASE=laravel/#DB_DATABASE=laravel/' .env
touch database/database.sqlite

# Package
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
git add .
git commit -m "Add Spatie Laravel Permissions package"
php artisan migrate:fresh

# Add `HasRoles` trait to User model
sed -i '' $'s/use Notifiable;/use Notifiable;\\\n    use \\\\Spatie\\\\Permission\\\\Traits\\\\HasRoles;/' app/User.php
git add . && git commit -m "Add HasRoles trait"

# Add Laravel's basic auth scaffolding
composer require laravel/ui --dev
php artisan ui bootstrap --auth
# npm install && npm run prod
git add . && git commit -m "Setup auth scaffold"
```

### Add some basic permissions
- Add a new file, `/database/seeds/PermissionsDemoSeeder.php` such as the following (You could create it with `php artisan make:seed` and then edit the file accordingly):

```php
<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'writer']);
        $role1->givePermissionTo('edit articles');
        $role1->givePermissionTo('delete articles');

        $role2 = Role::create(['name' => 'admin']);
        $role2->givePermissionTo('publish articles');
        $role2->givePermissionTo('unpublish articles');

        $role3 = Role::create(['name' => 'super-admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = Factory(App\User::class)->create([
            'name' => 'Example User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole($role1);

        $user = Factory(App\User::class)->create([
            'name' => 'Example Admin User',
            'email' => 'admin@example.com',
        ]);
        $user->assignRole($role2);

        $user = Factory(App\User::class)->create([
            'name' => 'Example Super-Admin User',
            'email' => 'superadmin@example.com',
        ]);
        $user->assignRole($role3);
    }
}

```

- re-migrate and seed the database:

```sh
composer dump-autoload
php artisan migrate:fresh --seed --seeder=PermissionsDemoSeeder
```

### Grant Super-Admin access
Super-Admins are a common feature. Using the following approach allows that when your Super-Admin user is logged in, all permission-checks in your app which call `can()` or `@can()` will return true.

- Add a Gate::before check in your `AuthServiceProvider`:

```diff
    public function boot()
    {
        $this->registerPolicies();
        
        //

+        // Implicitly grant "Super Admin" role all permission checks using can()
+        Gate::before(function ($user, $ability) {
+            if ($user->hasRole('Super-Admin')) {
+                return true;
+            }
+        });
    }
```


### Application Code
The permissions created in the seeder above imply that there will be some sort of Posts or Article features, and that various users will have various access control levels to manage/view those objects.

Your app will have Models, Controllers, routes, Views, Factories, Policies, Tests, middleware, and maybe additional Seeders.

You can see examples of these in the demo app at https://github.com/drbyte/spatie-permissions-demo/

## Sharing
To share your app on Github for easy collaboration:

- create a new public repository on Github, without any extras like readme/etc.
- follow github's sample code for linking your local repo and uploading the code. It will look like this:

```sh
git remote add origin git@github.com:YOURUSERNAME/REPONAME.git
git push -u origin master
```
The above only needs to be done once. 

- then add the rest of your code by making new commits:

```sh
git add .
git commit -m "Explain what your commit is about here"
git push origin master
```
Repeat the above process whenever you change code that you want to share.

Those are the basics!
