<?php

use Illuminate\Support\Facades\Artisan;
use function Pest\Laravel\artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('can create a role')
    ->tap(fn () => Artisan::call(
        'permission:create-role',
        ['name' => 'new-role']
    ))
    ->expect(fn () => Role::where('name', 'new-role')->get())
    ->toHaveCount(1)
    ->and(fn () => Role::where('name', 'new-role')->first()->permissions)
    ->toHaveCount(0);

it('can create a role with a specific guard', function () {
    Artisan::call('permission:create-role', [
        'name' => 'new-role',
        'guard' => 'api',
    ]);

    expect(
        Role::where('name', 'new-role')
            ->where('guard_name', 'api')
            ->get()
    )->toHaveCount(1);
});

it('can create a permission', function () {
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);

    expect(
        Permission::where('name', 'new-permission')->get()
    )->toHaveCount(1);
});

it('can create a permission with a specific guard', function () {
    Artisan::call('permission:create-permission', [
        'name' => 'new-permission',
        'guard' => 'api',
    ]);

    expect(
        Permission::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->get()
    )
        ->toHaveCount(1);
});

it('can create a role and permissions at same time', function () {
    Artisan::call('permission:create-role', [
        'name' => 'new-role',
        'permissions' => 'first permission | second permission',
    ]);

    $role = Role::where('name', 'new-role')->first();

    expect($role->hasPermissionTo('first permission'))
        ->and($role->hasPermissionTo('second permission'))
        ->toBeTrue();
});

it('can create a role without duplication', function () {
    Artisan::call('permission:create-role', ['name' => 'new-role']);
    Artisan::call('permission:create-role', ['name' => 'new-role']);

    expect(Role::where('name', 'new-role')->get())->toHaveCount(1)
        ->and(Role::where('name', 'new-role')->first()->permissions)->toHaveCount(0);
});

it('can create a permission without duplication', function () {
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);
    Artisan::call('permission:create-permission', ['name' => 'new-permission']);

    expect(Permission::where('name', 'new-permission')->get())->toHaveCount(1);
});

it('can show permission tables', function () {
    Artisan::call('permission:show');

    $output = Artisan::output();

    expect($output)->toContain('Guard: web', 'Guard: admin');

    expect($output)
        ->toMatch('/\|\s+\|\s+testRole\s+\|\s+testRole2\s+\|/') // |               | testRole | testRole2 |
        ->toMatch('/\|\s+edit-articles\s+\|\s+·\s+\|\s+·\s+\|/'); // | edit-articles |  ·       |  ·        |

    Role::findByName('testRole')->givePermissionTo('edit-articles');
    reloadPermissions();

    Artisan::call('permission:show');

    $output = Artisan::output();

    expect($output)
        ->toMatch('/\|\s+edit-articles\s+\|\s+✔\s+\|\s+·\s+\|/'); // | edit-articles |  ·       |  ·        |
});

it('can show permissions for guard', function () {
    Artisan::call('permission:show', ['guard' => 'web']);

    $output = Artisan::output();

    expect($output)
        ->toContain('Guard: web')
        ->not->toContain('Guard: admin');
});

it('can setup teams upgrade', function () {
    config()->set('permission.teams', true);

    artisan('permission:setup-teams')
        ->expectsQuestion('Proceed with the migration creation?', 'yes')
        ->assertExitCode(0);

    $matchingFiles = glob(database_path('migrations/*_add_teams_fields.php'));
    expect(count($matchingFiles))->toBeGreaterThan(0);

    include_once $matchingFiles[count($matchingFiles) - 1];
    (new \AddTeamsFields())->up();
    (new \AddTeamsFields())->up(); //test upgrade teams migration fresh

    Role::create(['name' => 'new-role', 'team_test_id' => 1]);
    $role = Role::where('name', 'new-role')->first();
    expect($role)->not->toBeNull()
        ->and((int) $role->team_test_id)->toBe(1);

    // remove migration
    foreach ($matchingFiles as $file) {
        unlink($file);
    }
});

it('can show roles by teams', function () {
    config()->set('permission.teams', true);
    app(\Spatie\Permission\PermissionRegistrar::class)->initializeCache();

    Role::create(['name' => 'testRoleTeam', 'team_test_id' => 1]);
    Role::create(['name' => 'testRoleTeam', 'team_test_id' => 2]); // same name different team
    Artisan::call('permission:show');

    $output = Artisan::output();

    expect($output)
        ->toMatch('/\|\s+\|\s+Team ID: NULL\s+\|\s+Team ID: 1\s+\|\s+Team ID: 2\s+\|/') // |    | Team ID: NULL        | Team ID: 1   | Team ID: 2   |
        ->toMatch('/\|\s+\|\s+testRole\s+\|\s+testRole2\s+\|\s+testRoleTeam\s+\|\s+testRoleTeam\s+\|/') // |    | testRole | testRole2 | testRoleTeam | testRoleTeam |
;
});
