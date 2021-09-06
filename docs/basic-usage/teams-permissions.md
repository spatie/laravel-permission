---
title: Teams permissions
weight: 3
---

NOTE: Those changes must be made before performing the migration. If you have already run the migration and want to upgrade your solution, you can run the artisan console command `php artisan permission:setup-teams`, to create a new migration file named [xxxx_xx_xx_xx_add_teams_fields.php](https://github.com/spatie/laravel-permission/blob/main/database/migrations/add_teams_fields.php.stub) and then run `php artisan migrate` to upgrade your database tables.

When enabled, teams permissions offers you a flexible control for a variety of scenarios. The idea behind teams permissions is inspired by the default permission implementation of [Laratrust](https://laratrust.santigarcor.me/).


Teams permissions can be enabled in the permission config file:

```php
// config/permission.php
'teams' => true,
```

Also, if you want to use a custom foreign key for teams you must change in the permission config file:
```php
// config/permission.php
'team_foreign_key' => 'custom_team_id',
```

## Working with Teams Permissions

After implements on login a solution for select a team on authentication (for example set `team_id` of the current selected team on **session**: `session(['team_id' => $team->team_id]);` ), 
we can set global `team_id` from anywhere, but works better if you create a `Middleware`, example:

```php
namespace App\Http\Middleware;

class TeamsPermission{
    
    public function handle($request, \Closure $next){
        if(!empty(auth()->user())){
            // session value set on login
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(session('team_id'));
        }
        // other custom ways to get team_id
        /*if(!empty(auth('api')->user())){
            // `getTeamIdFromToken()` example of custom method for getting the set team_id 
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(auth('api')->user()->getTeamIdFromToken());
        }*/
        
        return $next($request);
    }
}
```
NOTE: You must add your custom `Middleware` to `$middlewarePriority` on `app/Http/Kernel.php`.
 
## Roles Creating

When creating a role you can pass the `team_id` as an optional parameter
 
```php
// with null team_id it creates a global role, global roles can be assigned to any team and they are unique
Role::create(['name' => 'writer', 'team_id' => null]);

// creates a role with team_id = 1, team roles can have the same name on different teams
Role::create(['name' => 'reader', 'team_id' => 1]);

// creating a role without team_id makes the role take the default global team_id
Role::create(['name' => 'reviewer']);
```

## Roles/Permissions Assignment & Removal

The role/permission assignment and removal are the same, but they take the global `team_id` set on login for sync.

## Defining a Super-Admin on Teams

Global roles can be assigned to different teams, `team_id` as the primary key of the relationships is always required. If you want a "Super Admin" global role for a user, when you creates a new team you must assign it to your user. Example:

```php
namespace App\Models;

class YourTeamModel extends \Illuminate\Database\Eloquent\Model
{
    // ...
    public static function boot()
    {
        parent::boot();

        // here assign this team to a global user with global default role
        self::created(function ($model) {
           // get session team_id for restore it later
           $session_team_id = getPermissionsTeamId();
           // set actual new team_id to package instance
           setPermissionsTeamId($model);
           // get the admin user and assign roles/permissions on new team model
           User::find('your_user_id')->assignRole('Super Admin');
           // restore session team_id to package instance
           setPermissionsTeamId($session_team_id);
        }
    }
    // ...
}
```
