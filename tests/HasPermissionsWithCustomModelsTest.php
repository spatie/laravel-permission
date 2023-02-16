<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestModels\Permission;
use Spatie\Permission\Tests\TestModels\User;

include 'HasPermissionsTest.php';

trait SetupHasPermissionsWithCustomModelsTest {
    protected function getEnvironmentSetUp($app)
    {
        $this->useCustomModels = true;

        parent::getEnvironmentSetUp($app);
    }
}

uses(SetupHasPermissionsWithCustomModelsTest::class)->group('test');

it('can use custom model permission', function () {
    expect(Permission::class)->toBe(get_class($this->testUserPermission));
});

it('can use custom fields from cache', function () {
    DB::connection()->getSchemaBuilder()->table(config('permission.table_names.roles'), function ($table) {
        $table->string('type')->default('R');
    });
    DB::connection()->getSchemaBuilder()->table(config('permission.table_names.permissions'), function ($table) {
        $table->string('type')->default('P');
    });

    $this->testUserRole->givePermissionTo($this->testUserPermission);
    app(PermissionRegistrar::class)->getPermissions();

    DB::enableQueryLog();
    expect(Permission::findByName('edit-articles')->type)->toBe('P');
    expect(Permission::findByName('edit-articles')->roles[0]->type)->toBe('R');
    DB::disableQueryLog();

    expect(count(DB::getQueryLog()))->toBe(0);
});

it('can scope users using a uuid', function () {
    $uuid1 = $this->testUserPermission->getKey();
    $uuid2 = app(Permission::class)::where('name', 'edit-news')->first()->getKey();

    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->givePermissionTo([$uuid1, $uuid2]);
    $this->testUserRole->givePermissionTo($uuid1);
    $user2->assignRole('testRole');

    $scopedUsers1 = User::permission($uuid1)->get();
    $scopedUsers2 = User::permission([$uuid2])->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
});
