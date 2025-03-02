---
title: Example App
weight: 90
---

## Creating A Demo App

If you want to just try out the features of this package you can get started with the following.

The examples on this page are primarily added for assistance in creating a quick demo app for troubleshooting purposes, to post the repo on github for convenient sharing to collaborate or get support.

If you're new to Laravel or to any of the concepts mentioned here, you can learn more in the [Laravel documentation](https://laravel.com/docs/) and in the free videos at Laracasts such as with the [Laravel 11 in 30 days](https://laracasts.com/series/30-days-to-learn-laravel-11) or [Laravel 8 From Scratch](https://laracasts.com/series/laravel-8-from-scratch/) series.

### Initial setup:

```sh
cd ~/Sites
laravel new mypermissionsdemo
# (No Starter Kit is needed, but you could go with Livewire or Breeze/Jetstream, with Laravel's Built-In-Auth; or use Bootstrap using laravel/ui described later, below)
# (You might be asked to select a dark-mode-support choice)
# (Choose your desired testing framework: Pest or PHPUnit)
# (If offered, say Yes to initialize a Git repo, so that you can track your code changes)
# (If offered a database selection, choose SQLite, because it is simplest for test scenarios)
# (If prompted, say Yes to run default database migrations)
# (If prompted, say Yes to run npm install and related commands)

cd mypermissionsdemo

# The following git commands are not needed if you Initialized a git repo while "laravel new" was running above:
git init
git add .
git commit -m "Fresh Laravel Install"

# These Environment steps are not needed if you already selected SQLite while "laravel new" was running above:
cp -n .env.example .env
sed -i '' 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i '' 's/DB_DATABASE=/#DB_DATABASE=/' .env
touch database/database.sqlite

# Package
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
git add .
git commit -m "Add Spatie Laravel Permissions package"
php artisan migrate:fresh

# Add `HasRoles` trait to User model
sed -i '' $'s/use HasFactory, Notifiable;/use HasFactory, Notifiable;\\\n    use \\\\Spatie\\\\Permission\\\\Traits\\\\HasRoles;/' app/Models/User.php
sed -i '' $'s/use HasApiTokens, HasFactory, Notifiable;/use HasApiTokens, HasFactory, Notifiable;\\\n    use \\\\Spatie\\\\Permission\\\\Traits\\\\HasRoles;/' app/Models/User.php
git add . && git commit -m "Add HasRoles trait"
```

If you didn't install a Starter Kit like Livewire or Breeze or Jetstream, add Laravel's basic auth scaffolding:
This Auth scaffolding will make it simpler to provide login capability for a test/demo user, and test roles/permissions with them.
```php
composer require laravel/ui --dev
php artisan ui bootstrap --auth
# npm install && npm run build
git add . && git commit -m "Setup auth scaffold"
```

### Add some basic permissions
- Add a new file, `/database/seeders/PermissionsDemoSeeder.php` such as the following (You could create it with `php artisan make:seed` and then edit the file accordingly):

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
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

        $role3 = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = \App\Models\User::factory()->create([
            'name' => 'Example User',
            'email' => 'tester@example.com',
        ]);
        $user->assignRole($role1);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Admin User',
            'email' => 'admin@example.com',
        ]);
        $user->assignRole($role2);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Super-Admin User',
            'email' => 'superadmin@example.com',
        ]);
        $user->assignRole($role3);
    }
}

```

- re-migrate and seed the database:

```sh
php artisan migrate:fresh --seed --seeder=PermissionsDemoSeeder
```

### Grant Super-Admin access
Super-Admins are a common feature. The following approach allows that when your Super-Admin user is logged in, all permission-checks in your app which call `can()` or `@can()` will return true.

- Create a role named `Super-Admin`. (Or whatever name you wish; but use it consistently just like you must with any role name.)
- Add a Gate::before check in your `AuthServiceProvider` (or `AppServiceProvider` since Laravel 11):

```diff
+ use Illuminate\Support\Facades\Gate;

    public function boot()
    {
+        // Implicitly grant "Super-Admin" role all permission checks using can()
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


### Quick Examples
If you are creating a demo app for reporting a bug or getting help with troubleshooting something, skip this section and proceed to "Sharing" below.

If this is your first app with this package, you may want some quick permission examples to see it in action. If you've set up your app using the instructions above, the following examples will work in conjunction with the users and permissions created in the seeder.

Three users were created: tester@example.com, admin@example.com, superadmin@example.com and the password for each is "password".

`/resources/views/dashboard.php`
```diff
    <div class="p-6 text-gray-900">
        {{ __("You're logged in!") }}
    </div>
+    @can('edit articles')
+    You can EDIT ARTICLES.
+    @endcan
+    @can('publish articles')
+    You can PUBLISH ARTICLES.
+    @endcan
+    @can('only super-admins can see this section')
+    Congratulations, you are a super-admin!
+    @endcan
```
With the above code, when you login with each respective user, you will see different messages based on that access.

Here's a routes example with Breeze and Laravel 11. 
Edit `/routes/web.php`:
```diff
-Route::middleware('auth')->group(function () {
+Route::middleware('role_or_permission:publish articles')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
```
With the above change, you will be unable to access the user "Profile" page unless you are logged in with "admin" or "super-admin". You could change `role_or_permission:publish_articles` to `role:writer` to make it only available to the "test" user.

## Sharing
To share your app on Github for easy collaboration:

- create a new public repository on Github, without any extras like readme/etc.
- follow github's sample code for linking your local repo and uploading the code. It will look like this:

```sh
git remote add origin git@github.com:YOURUSERNAME/REPONAME.git
git push -u origin main
```
The above only needs to be done once. 

- then add the rest of your code by making new commits:

```sh
git add .
git commit -m "Explain what your commit is about here"
git push origin main
```
Repeat the above process whenever you change code that you want to share.

Those are the basics!
