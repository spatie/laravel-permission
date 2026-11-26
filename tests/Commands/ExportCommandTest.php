<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->exportDirectory = sys_get_temp_dir().'/laravel-permission-export-'.uniqid();
    $this->exportPath = $this->exportDirectory.'/permissions.json';
});

afterEach(function () {
    File::deleteDirectory($this->exportDirectory);
    File::delete(resource_path('js/permissions.json'));
});

function readExport(string $path): array
{
    return json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
}

it('exports permission and role names to the default path', function () {
    $exitCode = Artisan::call('permission:export');

    expect($exitCode)->toBe(0);
    expect(File::exists(resource_path('js/permissions.json')))->toBeTrue();
    expect(readExport(resource_path('js/permissions.json')))->toBe([
        'permissions' => ['Edit News', 'admin-permission', 'edit-articles', 'edit-blog', 'edit-news'],
        'roles' => ['testAdminRole', 'testRole', 'testRole2'],
    ]);
});

it('exports to a custom path and creates missing directories', function () {
    $path = $this->exportDirectory.'/nested/directory/acl.json';

    Artisan::call('permission:export', ['--path' => $path]);

    expect(File::exists($path))->toBeTrue();
    expect(Artisan::output())->toContain($path);
});

it('writes pretty printed json with a trailing newline', function () {
    Artisan::call('permission:export', ['--path' => $this->exportPath, '--only' => 'roles']);

    expect(File::get($this->exportPath))->toBe(<<<'JSON'
        {
            "roles": [
                "testAdminRole",
                "testRole",
                "testRole2"
            ]
        }

        JSON);
});

it('can export the names for a specific guard', function () {
    Permission::create(['name' => 'edit-articles', 'guard_name' => 'api']);
    Permission::create(['name' => 'manage-tokens', 'guard_name' => 'api']);
    Role::create(['name' => 'api-client', 'guard_name' => 'api']);

    Artisan::call('permission:export', ['--path' => $this->exportPath, '--guard' => 'api']);

    expect(readExport($this->exportPath))->toBe([
        'permissions' => ['edit-articles', 'manage-tokens'],
        'roles' => ['api-client'],
    ]);
});

it('exports the names of all guards sorted and without duplicates', function () {
    Permission::create(['name' => 'edit-articles', 'guard_name' => 'api']);
    Permission::create(['name' => 'a-permission', 'guard_name' => 'admin']);
    Role::create(['name' => 'testRole', 'guard_name' => 'api']);
    Role::create(['name' => 'author']);

    Artisan::call('permission:export', ['--path' => $this->exportPath]);

    expect(readExport($this->exportPath))->toBe([
        'permissions' => ['Edit News', 'a-permission', 'admin-permission', 'edit-articles', 'edit-blog', 'edit-news'],
        'roles' => ['author', 'testAdminRole', 'testRole', 'testRole2'],
    ]);
});

it('can export only permissions', function () {
    Artisan::call('permission:export', ['--path' => $this->exportPath, '--only' => 'permissions']);

    expect(readExport($this->exportPath))->toBe([
        'permissions' => ['Edit News', 'admin-permission', 'edit-articles', 'edit-blog', 'edit-news'],
    ]);
});

it('can export only roles', function () {
    Artisan::call('permission:export', ['--path' => $this->exportPath, '--only' => 'roles']);

    expect(readExport($this->exportPath))->toBe([
        'roles' => ['testAdminRole', 'testRole', 'testRole2'],
    ]);
});

it('fails when the only option is invalid', function () {
    $exitCode = Artisan::call('permission:export', ['--path' => $this->exportPath, '--only' => 'users']);

    expect($exitCode)->toBe(1);
    expect(Artisan::output())->toContain('The --only option must be either "permissions" or "roles".');
    expect(File::exists($this->exportPath))->toBeFalse();
});

it('exports empty lists when there are no permissions or roles', function () {
    Permission::query()->delete();
    Role::query()->delete();

    Artisan::call('permission:export', ['--path' => $this->exportPath]);

    expect(File::get($this->exportPath))->toBe(<<<'JSON'
        {
            "permissions": [],
            "roles": []
        }

        JSON);
});

it('exports empty lists for a guard without permissions or roles', function () {
    Artisan::call('permission:export', ['--path' => $this->exportPath, '--guard' => 'api']);

    expect(readExport($this->exportPath))->toBe([
        'permissions' => [],
        'roles' => [],
    ]);
});

it('produces identical output on repeated exports', function () {
    Artisan::call('permission:export', ['--path' => $this->exportPath]);
    $first = File::get($this->exportPath);

    Artisan::call('permission:export', ['--path' => $this->exportPath]);

    expect(File::get($this->exportPath))->toBe($first);
});
