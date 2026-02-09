<?php

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

it('can create a role', function () {
    Artisan::call('permission:create-role', ['name' => 'new-role']);

    expect(Role::where('name', 'new-role')->get())->toHaveCount(1);
    expect(Role::where('name', 'new-role')->first()->permissions)->toHaveCount(0);
});

it('can create a role with a specific guard', function () {
    Artisan::call('permission:create-role', [
        'name' => 'new-role',
        'guard' => 'api',
    ]);

    expect(Role::where('name', 'new-role')->where('guard_name', 'api')->get())->toHaveCount(1);
});

it('can create a permission', function () {
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);

    expect(Permission::where('name', 'new-permission')->get())->toHaveCount(1);
});

it('can create a permission with a specific guard', function () {
    Artisan::call('permission:create-permission', [
        'name' => 'new-permission',
        'guard' => 'api',
    ]);

    expect(Permission::where('name', 'new-permission')->where('guard_name', 'api')->get())->toHaveCount(1);
});

it('can create a role and permissions at same time', function () {
    Artisan::call('permission:create-role', [
        'name' => 'new-role',
        'permissions' => 'first permission | second permission',
    ]);

    $role = Role::where('name', 'new-role')->first();

    expect($role->hasPermissionTo('first permission'))->toBeTrue();
    expect($role->hasPermissionTo('second permission'))->toBeTrue();
});

it('can create a role without duplication', function () {
    Artisan::call('permission:create-role', ['name' => 'new-role']);
    Artisan::call('permission:create-role', ['name' => 'new-role']);

    expect(Role::where('name', 'new-role')->get())->toHaveCount(1);
    expect(Role::where('name', 'new-role')->first()->permissions)->toHaveCount(0);
});

it('can create a permission without duplication', function () {
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);

    expect(Permission::where('name', 'new-permission')->get())->toHaveCount(1);
});

it('can show permission tables', function () {
    Role::where('name', 'testRole2')->delete();
    Role::create(['name' => 'testRole_2']);

    Artisan::call('permission:show');

    $output = Artisan::output();

    expect(strpos($output, 'Guard: web'))->not->toBeFalse();
    expect(strpos($output, 'Guard: admin'))->not->toBeFalse();

    expect($output)->toMatch('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|/');
    expect($output)->toMatch('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/');

    Role::findByName('testRole')->givePermissionTo('edit-articles');
    $this->reloadPermissions();

    Artisan::call('permission:show');

    $output = Artisan::output();

    expect($output)->toMatch('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/');
});

it('can show permissions for guard', function () {
    Artisan::call('permission:show', ['guard' => 'web']);

    $output = Artisan::output();

    expect(strpos($output, 'Guard: web'))->not->toBeFalse();
    expect(strpos($output, 'Guard: admin'))->toBeFalse();
});

it('can setup teams upgrade', function () {
    config()->set('permission.teams', true);

    $this->artisan('permission:setup-teams')
        ->expectsQuestion('Proceed with the migration creation?', 'yes')
        ->assertExitCode(0);

    $matchingFiles = glob(database_path('migrations/*_add_teams_fields.php'));
    expect(count($matchingFiles) > 0)->toBeTrue();

    $AddTeamsFields = require $matchingFiles[count($matchingFiles) - 1];
    $AddTeamsFields->up();
    $AddTeamsFields->up(); // test upgrade teams migration fresh

    Role::create(['name' => 'new-role', 'team_test_id' => 1]);
    $role = Role::where('name', 'new-role')->first();
    expect($role)->not->toBeNull();
    expect((int) $role->team_test_id)->toBe(1);

    // remove migration
    foreach ($matchingFiles as $file) {
        unlink($file);
    }
});

it('can show roles by teams', function () {
    config()->set('permission.teams', true);
    app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

    Role::where('name', 'testRole2')->delete();
    Role::create(['name' => 'testRole_2']);
    Role::create(['name' => 'testRole_Team', 'team_test_id' => 1]);
    Role::create(['name' => 'testRole_Team', 'team_test_id' => 2]); // same name different team
    Artisan::call('permission:show');

    $output = Artisan::output();

    expect($output)->toMatch('/\|\s+\|\s+Team ID: NULL\s+\|\s+Team ID: 1\s+\|\s+Team ID: 2\s+\|/');
    expect($output)->toMatch('/\|\s+\|\s+testRole\s+\|\s+testRole_2\s+\|\s+testRole_Team\s+\|\s+testRole_Team\s+\|/');
});

it('can respond to about command with default', function () {
    if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
        $this->markTestSkipped();
    }
    if (! method_exists(AboutCommand::class, 'flushState')) {
        $this->markTestSkipped();
    }

    app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

    Artisan::call('about');
    $output = str_replace("\r\n", "\n", Artisan::output());

    $pattern = '/Spatie Permissions[ .\n]*Features Enabled[ .]*Default[ .\n]*Version/';
    expect($output)->toMatch($pattern);
});

it('can respond to about command with teams', function () {
    if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
        $this->markTestSkipped();
    }
    if (! method_exists(AboutCommand::class, 'flushState')) {
        $this->markTestSkipped();
    }

    app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

    config()->set('permission.teams', true);

    Artisan::call('about');
    $output = str_replace("\r\n", "\n", Artisan::output());

    $pattern = '/Spatie Permissions[ .\n]*Features Enabled[ .]*Teams[ .\n]*Version/';
    expect($output)->toMatch($pattern);
});

it('can assign role to user', function () {
    $user = User::first();

    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => $user->id,
        'guard' => 'web',
        'userModelNamespace' => User::class,
    ]);

    $output = Artisan::output();

    expect($output)->toContain('Role `testRole` assigned to user ID '.$user->id.' successfully.');
    expect(Role::where('name', 'testRole')->get())->toHaveCount(1);
    expect($user->roles)->toHaveCount(1);
    expect($user->hasRole('testRole'))->toBeTrue();
});

it('fails to assign role when user not found', function () {
    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => 99999,
        'guard' => 'web',
        'userModelNamespace' => User::class,
    ]);

    $output = Artisan::output();

    expect($output)->toContain('User with ID 99999 not found.');
});

it('fails to assign role when namespace invalid', function () {
    $user = User::first();

    $userModelClass = 'App\Models\NonExistentUser';

    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => $user->id,
        'guard' => 'web',
        'userModelNamespace' => $userModelClass,
    ]);

    $output = Artisan::output();

    expect($output)->toContain("User model class [{$userModelClass}] does not exist.");
});

it('warns when assigning role with team id but teams disabled', function () {
    $user = User::first();

    Artisan::call('permission:assign-role', [
        'name' => 'testRole',
        'userId' => $user->id,
        'userModelNamespace' => User::class,
        '--team-id' => 1,
    ]);

    $output = Artisan::output();

    expect($output)->toContain('Teams feature disabled');
});
