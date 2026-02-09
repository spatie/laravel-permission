<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\Admin;
use Spatie\Permission\Tests\TestSupport\TestModels\SoftDeletingUser;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

it('can determine that the user does not have a role', function () {
    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    $role = app(Role::class)->findOrCreate('testRoleInWebGuard', 'web');

    expect($this->testUser->hasRole($role))->toBeFalse();

    $this->testUser->assignRole($role);
    expect($this->testUser->hasRole($role))->toBeTrue();
    expect($this->testUser->hasRole($role->name))->toBeTrue();
    expect($this->testUser->hasRole($role->name, $role->guard_name))->toBeTrue();
    expect($this->testUser->hasRole([$role->name, 'fakeRole'], $role->guard_name))->toBeTrue();
    expect($this->testUser->hasRole($role->getKey(), $role->guard_name))->toBeTrue();
    expect($this->testUser->hasRole([$role->getKey(), 'fakeRole'], $role->guard_name))->toBeTrue();

    expect($this->testUser->hasRole($role->name, 'fakeGuard'))->toBeFalse();
    expect($this->testUser->hasRole([$role->name, 'fakeRole'], 'fakeGuard'))->toBeFalse();
    expect($this->testUser->hasRole($role->getKey(), 'fakeGuard'))->toBeFalse();
    expect($this->testUser->hasRole([$role->getKey(), 'fakeRole'], 'fakeGuard'))->toBeFalse();

    $role = app(Role::class)->findOrCreate('testRoleInWebGuard2', 'web');
    expect($this->testUser->hasRole($role))->toBeFalse();
});

it('can assign and remove a role using enums', function () {
    $enum1 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::UserManager;
    $enum2 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::Writer;
    $enum3 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::CastedEnum1;
    $enum4 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::CastedEnum2;

    app(Role::class)->findOrCreate($enum1->value, 'web');
    app(Role::class)->findOrCreate($enum2->value, 'web');
    app(Role::class)->findOrCreate($enum3->value, 'web');
    app(Role::class)->findOrCreate($enum4->value, 'web');

    expect($this->testUser->hasRole($enum1))->toBeFalse();
    expect($this->testUser->hasRole($enum2))->toBeFalse();
    expect($this->testUser->hasRole($enum3))->toBeFalse();
    expect($this->testUser->hasRole($enum4))->toBeFalse();
    expect($this->testUser->hasRole('user-manager'))->toBeFalse();
    expect($this->testUser->hasRole('writer'))->toBeFalse();
    expect($this->testUser->hasRole('casted_enum-1'))->toBeFalse();
    expect($this->testUser->hasRole('casted_enum-2'))->toBeFalse();

    $this->testUser->assignRole($enum1);
    $this->testUser->assignRole($enum2);
    $this->testUser->assignRole($enum3);
    $this->testUser->assignRole($enum4);

    expect($this->testUser->hasRole($enum1))->toBeTrue();
    expect($this->testUser->hasRole($enum2))->toBeTrue();
    expect($this->testUser->hasRole($enum3))->toBeTrue();
    expect($this->testUser->hasRole($enum4))->toBeTrue();

    expect($this->testUser->hasRole([$enum1, 'writer']))->toBeTrue();
    expect($this->testUser->hasRole([$enum3, 'casted_enum-2']))->toBeTrue();

    expect($this->testUser->hasAllRoles([$enum1, $enum2, $enum3, $enum4]))->toBeTrue();
    expect($this->testUser->hasAllRoles(['user-manager', 'writer', 'casted_enum-1', 'casted_enum-2']))->toBeTrue();
    expect($this->testUser->hasAllRoles([$enum1, $enum2, $enum3, $enum4, 'not exist']))->toBeFalse();
    expect($this->testUser->hasAllRoles(['user-manager', 'writer', 'casted_enum-1', 'casted_enum-2', 'not exist']))->toBeFalse();

    expect($this->testUser->hasExactRoles([$enum4, $enum3, $enum2, $enum1]))->toBeTrue();
    expect($this->testUser->hasExactRoles(['user-manager', 'writer', 'casted_enum-1', 'casted_enum-2']))->toBeTrue();

    $this->testUser->removeRole($enum1);

    expect($this->testUser->hasRole($enum1))->toBeFalse();
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('can scope a role using enums', function () {
    $enum1 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::UserManager;
    $enum2 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::Writer;
    $role1 = app(Role::class)->findOrCreate($enum1->value, 'web');
    $role2 = app(Role::class)->findOrCreate($enum2->value, 'web');

    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);

    // assign only one user to a role
    $user2->assignRole($enum1);
    expect($user2->hasRole($enum1))->toBeTrue();
    expect($user2->hasRole($enum2))->toBeFalse();

    $scopedUsers1 = User::role($enum1)->get();
    $scopedUsers2 = User::role($enum2)->get();
    $scopedUsers3 = User::withoutRole($enum2)->get();

    expect($scopedUsers1->count())->toEqual(1);
    expect($scopedUsers2->count())->toEqual(0);
    expect($scopedUsers3->count())->toEqual(3);
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('can assign and remove a role', function () {
    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasRole('testRole'))->toBeTrue();

    $this->testUser->removeRole('testRole');

    expect($this->testUser->hasRole('testRole'))->toBeFalse();
});

it('removes a role and returns roles', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->assignRole('testRole2');

    expect($this->testUser->hasRole(['testRole', 'testRole2']))->toBeTrue();

    $roles = $this->testUser->removeRole('testRole');

    expect($roles->hasRole('testRole'))->toBeFalse();

    expect($roles->hasRole('testRole2'))->toBeTrue();
});

it('can assign and remove a role on a permission', function () {
    $this->testUserPermission->assignRole('testRole');

    expect($this->testUserPermission->hasRole('testRole'))->toBeTrue();

    $this->testUserPermission->removeRole('testRole');

    expect($this->testUserPermission->hasRole('testRole'))->toBeFalse();
});

it('can assign and remove a role using an object', function () {
    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasRole($this->testUserRole))->toBeTrue();

    $this->testUser->removeRole($this->testUserRole);

    expect($this->testUser->hasRole($this->testUserRole))->toBeFalse();
});

it('can assign and remove a role using an id', function () {
    $this->testUser->assignRole($this->testUserRole->getKey());

    expect($this->testUser->hasRole($this->testUserRole))->toBeTrue();

    $this->testUser->removeRole($this->testUserRole->getKey());

    expect($this->testUser->hasRole($this->testUserRole))->toBeFalse();
});

it('can assign and remove multiple roles at once', function () {
    $this->testUser->assignRole($this->testUserRole->getKey(), 'testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeTrue();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();

    $this->testUser->removeRole($this->testUserRole->getKey(), 'testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    expect($this->testUser->hasRole('testRole2'))->toBeFalse();
});

it('can assign and remove multiple roles using an array', function () {
    $this->testUser->assignRole([$this->testUserRole->getKey(), 'testRole2']);

    expect($this->testUser->hasRole('testRole'))->toBeTrue();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();

    $this->testUser->removeRole([$this->testUserRole->getKey(), 'testRole2']);

    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    expect($this->testUser->hasRole('testRole2'))->toBeFalse();
});

it('does not remove already associated roles when assigning new roles', function () {
    $this->testUser->assignRole($this->testUserRole->getKey());

    $this->testUser->assignRole('testRole2');

    expect($this->testUser->fresh()->hasRole('testRole'))->toBeTrue();
});

it('does not throw an exception when assigning a role that is already assigned', function () {
    $this->testUser->assignRole($this->testUserRole->getKey());

    $this->testUser->assignRole($this->testUserRole->getKey());

    expect($this->testUser->fresh()->hasRole('testRole'))->toBeTrue();
});

it('throws an exception when assigning a role that does not exist', function () {
    $this->expectException(RoleDoesNotExist::class);

    $this->testUser->assignRole('evil-emperor');
});

it('can only assign roles from the correct guard', function () {
    $this->expectException(RoleDoesNotExist::class);

    $this->testUser->assignRole('testAdminRole');
});

it('throws an exception when assigning a role from a different guard', function () {
    $this->expectException(GuardDoesNotMatch::class);

    $this->testUser->assignRole($this->testAdminRole);
});

it('ignores null roles when syncing', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->syncRoles('testRole2', null);

    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can sync roles from a string', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->syncRoles('testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can sync roles from a string on a permission', function () {
    $this->testUserPermission->assignRole('testRole');

    $this->testUserPermission->syncRoles('testRole2');

    expect($this->testUserPermission->hasRole('testRole'))->toBeFalse();

    expect($this->testUserPermission->hasRole('testRole2'))->toBeTrue();
});

it('can avoid sync duplicated roles', function () {
    $this->testUser->syncRoles('testRole', 'testRole', 'testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeTrue();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can avoid detach on role that does not exist sync', function () {
    $this->testUser->syncRoles('testRole');

    try {
        $this->testUser->syncRoles('role-does-not-exist');
        $this->fail('Expected RoleDoesNotExist exception was not thrown.');
    } catch (RoleDoesNotExist $e) {
        //
    }

    expect($this->testUser->hasRole('testRole'))->toBeTrue();
    expect($this->testUser->hasRole('role-does-not-exist'))->toBeFalse();
});

it('can sync multiple roles', function () {
    $this->testUser->syncRoles('testRole', 'testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeTrue();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can sync multiple roles from an array', function () {
    $this->testUser->syncRoles(['testRole', 'testRole2']);

    expect($this->testUser->hasRole('testRole'))->toBeTrue();

    expect($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('will remove all roles when an empty array is passed to sync roles', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->assignRole('testRole2');

    $this->testUser->syncRoles([]);

    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    expect($this->testUser->hasRole('testRole2'))->toBeFalse();
});

it('sync roles error does not detach roles', function () {
    $this->testUser->assignRole('testRole');

    $this->expectException(RoleDoesNotExist::class);

    $this->testUser->syncRoles('testRole2', 'role-that-does-not-exist');

    expect($this->testUser->fresh()->hasRole('testRole'))->toBeTrue();
});

it('will sync roles to a model that is not persisted', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->syncRoles([$this->testUserRole]);
    $user->save();
    $user->save(); // test save same model twice

    expect($user->hasRole($this->testUserRole))->toBeTrue();

    $user->syncRoles([$this->testUserRole]);
    expect($user->hasRole($this->testUserRole))->toBeTrue();
    expect($user->fresh()->hasRole($this->testUserRole))->toBeTrue();
});

it('does not run unnecessary sqls when assigning new roles', function () {
    $role2 = app(Role::class)->where('name', ['testRole2'])->first();

    DB::enableQueryLog();
    $this->testUser->syncRoles($this->testUserRole, $role2);
    DB::disableQueryLog();

    $necessaryQueriesCount = 2;

    // Teams reloads relation, adding an extra query
    if (app(PermissionRegistrar::class)->teams) {
        $necessaryQueriesCount++;
    }

    expect(DB::getQueryLog())->toHaveCount($necessaryQueriesCount);
});

it('calling syncRoles before saving object doesnt interfere with other objects', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->syncRoles('testRole');
    $user->save();

    $user2 = new User(['email' => 'admin@user.com']);
    $user2->syncRoles('testRole2');

    DB::enableQueryLog();
    $user2->save();
    DB::disableQueryLog();

    expect($user->fresh()->hasRole('testRole'))->toBeTrue();
    expect($user->fresh()->hasRole('testRole2'))->toBeFalse();

    expect($user2->fresh()->hasRole('testRole2'))->toBeTrue();
    expect($user2->fresh()->hasRole('testRole'))->toBeFalse();
    expect(count(DB::getQueryLog()))->toBe(2); // avoid unnecessary sync
});

it('calling assignRole before saving object doesnt interfere with other objects', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->assignRole('testRole');
    $user->save();

    $admin_user = new User(['email' => 'admin@user.com']);
    $admin_user->assignRole('testRole2');

    DB::enableQueryLog();
    $admin_user->save();
    DB::disableQueryLog();

    expect($user->fresh()->hasRole('testRole'))->toBeTrue();
    expect($user->fresh()->hasRole('testRole2'))->toBeFalse();

    expect($admin_user->fresh()->hasRole('testRole2'))->toBeTrue();
    expect($admin_user->fresh()->hasRole('testRole'))->toBeFalse();
    expect(count(DB::getQueryLog()))->toBe(2); // avoid unnecessary sync
});

it('throws an exception when syncing a role from another guard', function () {
    $this->expectException(RoleDoesNotExist::class);

    $this->testUser->syncRoles('testRole', 'testAdminRole');

    $this->expectException(GuardDoesNotMatch::class);

    $this->testUser->syncRoles('testRole', $this->testAdminRole);
});

it('deletes pivot table entries when deleting models', function () {
    $user = User::create(['email' => 'user@test.com']);

    $user->assignRole('testRole');
    $user->givePermissionTo('edit-articles');

    $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user->id]);
    $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.model_morph_key') => $user->id]);

    $user->delete();

    $this->assertDatabaseMissing('model_has_permissions', [config('permission.column_names.model_morph_key') => $user->id]);
    $this->assertDatabaseMissing('model_has_roles', [config('permission.column_names.model_morph_key') => $user->id]);
});

it('can scope users using a string', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole2');

    $scopedUsers = User::role('testRole')->get();

    expect($scopedUsers->count())->toEqual(1);
});

it('can withoutscope users using a string', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole2');
    $user3->assignRole('testRole2');

    $scopedUsers = User::withoutRole('testRole2')->get();

    expect($scopedUsers->count())->toEqual(1);
});

it('can scope users using an array', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role([$this->testUserRole])->get();
    $scopedUsers2 = User::role(['testRole', 'testRole2'])->get();

    expect($scopedUsers1->count())->toEqual(1);
    expect($scopedUsers2->count())->toEqual(2);
});

it('can withoutscope users using an array', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::withoutRole([$this->testUserRole])->get();
    $scopedUsers2 = User::withoutRole([$this->testUserRole->name, 'testRole2'])->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(0);
});

it('can scope users using an array of ids and names', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $firstAssignedRoleName = $this->testUserRole->name;
    $secondAssignedRoleId = app(Role::class)->findByName('testRole2')->getKey();

    $scopedUsers = User::role([$firstAssignedRoleName, $secondAssignedRoleId])->get();

    expect($scopedUsers->count())->toEqual(2);
});

it('can withoutscope users using an array of ids and names', function () {
    app(Role::class)->create(['name' => 'testRole3']);

    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');
    $user3->assignRole('testRole2');

    $firstAssignedRoleName = $this->testUserRole->name;
    $unassignedRoleId = app(Role::class)->findByName('testRole3')->getKey();

    $scopedUsers = User::withoutRole([$firstAssignedRoleName, $unassignedRoleId])->get();

    expect($scopedUsers->count())->toEqual(2);
});

it('can scope users using a collection', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role([$this->testUserRole])->get();
    $scopedUsers2 = User::role(collect(['testRole', 'testRole2']))->get();

    expect($scopedUsers1->count())->toEqual(1);
    expect($scopedUsers2->count())->toEqual(2);
});

it('can withoutscope users using a collection', function () {
    app(Role::class)->create(['name' => 'testRole3']);

    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::withoutRole([$this->testUserRole])->get();
    $scopedUsers2 = User::withoutRole(collect(['testRole', 'testRole3']))->get();

    expect($scopedUsers1->count())->toEqual(1);
    expect($scopedUsers2->count())->toEqual(1);
});

it('can scope users using an object', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role($this->testUserRole)->get();
    $scopedUsers2 = User::role([$this->testUserRole])->get();
    $scopedUsers3 = User::role(collect([$this->testUserRole]))->get();

    expect($scopedUsers1->count())->toEqual(1);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(1);
});

it('can withoutscope users using an object', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::withoutRole($this->testUserRole)->get();
    $scopedUsers2 = User::withoutRole([$this->testUserRole])->get();
    $scopedUsers3 = User::withoutRole(collect([$this->testUserRole]))->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(2);
    expect($scopedUsers3->count())->toEqual(2);
});

it('can scope against a specific guard', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role('testRole', 'web')->get();

    expect($scopedUsers1->count())->toEqual(1);

    $user3 = Admin::create(['email' => 'user3@test.com']);
    $user4 = Admin::create(['email' => 'user4@test.com']);
    $user5 = Admin::create(['email' => 'user5@test.com']);
    $testAdminRole2 = app(Role::class)->create(['name' => 'testAdminRole2', 'guard_name' => 'admin']);
    $user3->assignRole($this->testAdminRole);
    $user4->assignRole($this->testAdminRole);
    $user5->assignRole($testAdminRole2);
    $scopedUsers2 = Admin::role('testAdminRole', 'admin')->get();
    $scopedUsers3 = Admin::role('testAdminRole2', 'admin')->get();

    expect($scopedUsers2->count())->toEqual(2);
    expect($scopedUsers3->count())->toEqual(1);
});

it('can withoutscope against a specific guard', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole2');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::withoutRole('testRole', 'web')->get();

    expect($scopedUsers1->count())->toEqual(2);

    Admin::all()->each(fn ($item) => $item->delete());
    $user4 = Admin::create(['email' => 'user4@test.com']);
    $user5 = Admin::create(['email' => 'user5@test.com']);
    $user6 = Admin::create(['email' => 'user6@test.com']);
    $testAdminRole2 = app(Role::class)->create(['name' => 'testAdminRole2', 'guard_name' => 'admin']);
    $user4->assignRole($this->testAdminRole);
    $user5->assignRole($this->testAdminRole);
    $user6->assignRole($testAdminRole2);
    $scopedUsers2 = Admin::withoutRole('testAdminRole', 'admin')->get();
    $scopedUsers3 = Admin::withoutRole('testAdminRole2', 'admin')->get();

    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(2);
});

it('throws an exception when trying to scope a role from another guard', function () {
    $this->expectException(RoleDoesNotExist::class);

    User::role('testAdminRole')->get();

    $this->expectException(GuardDoesNotMatch::class);

    User::role($this->testAdminRole)->get();
});

it('throws an exception when trying to call withoutscope on a role from another guard', function () {
    $this->expectException(RoleDoesNotExist::class);

    User::withoutRole('testAdminRole')->get();

    $this->expectException(GuardDoesNotMatch::class);

    User::withoutRole($this->testAdminRole)->get();
});

it('throws an exception when trying to scope a non existing role', function () {
    $this->expectException(RoleDoesNotExist::class);

    User::role('role not defined')->get();
});

it('throws an exception when trying to use withoutscope on a non existing role', function () {
    $this->expectException(RoleDoesNotExist::class);

    User::withoutRole('role not defined')->get();
});

it('can determine that a user has one of the given roles', function () {
    $roleModel = app(Role::class);

    $roleModel->create(['name' => 'second role']);

    expect($this->testUser->hasRole($roleModel->all()))->toBeFalse();

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasRole($roleModel->all()))->toBeTrue();

    expect($this->testUser->hasAnyRole($roleModel->all()))->toBeTrue();

    expect($this->testUser->hasAnyRole('testRole'))->toBeTrue();

    expect($this->testUser->hasAnyRole('role does not exist'))->toBeFalse();

    expect($this->testUser->hasAnyRole(['testRole']))->toBeTrue();

    expect($this->testUser->hasAnyRole(['testRole', 'role does not exist']))->toBeTrue();

    expect($this->testUser->hasAnyRole(['role does not exist']))->toBeFalse();

    expect($this->testUser->hasAnyRole('testRole', 'role does not exist'))->toBeTrue();
});

it('can determine that a user has all of the given roles', function () {
    $roleModel = app(Role::class);

    expect($this->testUser->hasAllRoles($roleModel->first()))->toBeFalse();

    expect($this->testUser->hasAllRoles('testRole'))->toBeFalse();

    expect($this->testUser->hasAllRoles($roleModel->all()))->toBeFalse();

    $roleModel->create(['name' => 'second role']);

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasAllRoles('testRole'))->toBeTrue();
    expect($this->testUser->hasAllRoles('testRole', 'web'))->toBeTrue();
    expect($this->testUser->hasAllRoles('testRole', 'fakeGuard'))->toBeFalse();

    expect($this->testUser->hasAllRoles(['testRole', 'second role']))->toBeFalse();
    expect($this->testUser->hasAllRoles(['testRole', 'second role'], 'web'))->toBeFalse();

    $this->testUser->assignRole('second role');

    expect($this->testUser->hasAllRoles(['testRole', 'second role']))->toBeTrue();
    expect($this->testUser->hasAllRoles(['testRole', 'second role'], 'web'))->toBeTrue();
    expect($this->testUser->hasAllRoles(['testRole', 'second role'], 'fakeGuard'))->toBeFalse();
});

it('can determine that a user has exact all of the given roles', function () {
    $roleModel = app(Role::class);

    expect($this->testUser->hasExactRoles($roleModel->first()))->toBeFalse();

    expect($this->testUser->hasExactRoles('testRole'))->toBeFalse();

    expect($this->testUser->hasExactRoles($roleModel->all()))->toBeFalse();

    $roleModel->create(['name' => 'second role']);

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasExactRoles('testRole'))->toBeTrue();
    expect($this->testUser->hasExactRoles('testRole', 'web'))->toBeTrue();
    expect($this->testUser->hasExactRoles('testRole', 'fakeGuard'))->toBeFalse();

    expect($this->testUser->hasExactRoles(['testRole', 'second role']))->toBeFalse();
    expect($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'))->toBeFalse();

    $this->testUser->assignRole('second role');

    expect($this->testUser->hasExactRoles(['testRole', 'second role']))->toBeTrue();
    expect($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'))->toBeTrue();
    expect($this->testUser->hasExactRoles(['testRole', 'second role'], 'fakeGuard'))->toBeFalse();

    $roleModel->create(['name' => 'third role']);
    $this->testUser->assignRole('third role');

    expect($this->testUser->hasExactRoles(['testRole', 'second role']))->toBeFalse();
    expect($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'))->toBeFalse();
    expect($this->testUser->hasExactRoles(['testRole', 'second role'], 'fakeGuard'))->toBeFalse();
    expect($this->testUser->hasExactRoles(['testRole', 'second role', 'third role']))->toBeTrue();
    expect($this->testUser->hasExactRoles(['testRole', 'second role', 'third role'], 'web'))->toBeTrue();
    expect($this->testUser->hasExactRoles(['testRole', 'second role', 'third role'], 'fakeGuard'))->toBeFalse();
});

it('can determine that a user does not have a role from another guard', function () {
    expect($this->testUser->hasRole('testAdminRole'))->toBeFalse();

    expect($this->testUser->hasRole($this->testAdminRole))->toBeFalse();

    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasAnyRole(['testRole', 'testAdminRole']))->toBeTrue();

    expect($this->testUser->hasAnyRole('testAdminRole', $this->testAdminRole))->toBeFalse();
});

it('can check against any multiple roles using multiple arguments', function () {
    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasAnyRole($this->testAdminRole, ['testRole'], 'This Role Does Not Even Exist'))->toBeTrue();
});

it('returns false instead of an exception when checking against any undefined roles using multiple arguments', function () {
    expect($this->testUser->hasAnyRole('This Role Does Not Even Exist', $this->testAdminRole))->toBeFalse();
});

it('throws an exception if an unsupported type is passed to hasRoles', function () {
    $this->expectException(\TypeError::class);

    $this->testUser->hasRole(new class {});
});

it('can retrieve role names', function () {
    $this->testUser->assignRole('testRole', 'testRole2');

    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(
        collect(['testRole', 'testRole2'])
    );
});

it('does not detach roles when user soft deleting', function () {
    $user = SoftDeletingUser::create(['email' => 'test@example.com']);
    $user->assignRole('testRole');
    $user->delete();

    $user = SoftDeletingUser::withTrashed()->find($user->id);

    expect($user->hasRole('testRole'))->toBeTrue();
});

it('fires an event when a role is added', function () {
    Event::fake();
    app('config')->set('permission.events_enabled', true);

    $this->testUser->assignRole(['testRole', 'testRole2']);

    $roleIds = app(Role::class)::whereIn('name', ['testRole', 'testRole2'])
        ->pluck($this->testUserRole->getKeyName())
        ->toArray();

    Event::assertDispatched(RoleAttached::class, function ($event) use ($roleIds) {
        return $event->model instanceof User
            && $event->model->hasRole('testRole')
            && $event->model->hasRole('testRole2')
            && $event->rolesOrIds === $roleIds;
    });
});

it('fires an event when a role is removed', function () {
    Event::fake();
    app('config')->set('permission.events_enabled', true);

    $this->testUser->assignRole('testRole', 'testRole2');

    $this->testUser->removeRole('testRole', 'testRole2');

    $roleIds = app(Role::class)::whereIn('name', ['testRole', 'testRole2'])
        ->pluck($this->testUserRole->getKeyName())
        ->toArray();

    Event::assertDispatched(RoleDetached::class, function ($event) use ($roleIds) {
        return $event->model instanceof User
            && ! $event->model->hasRole('testRole')
            && ! $event->model->hasRole('testRole2')
            && $event->rolesOrIds === $roleIds;
    });
});

it('can be given a role on permission when lazy loading is restricted', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();

    try {
        $testPermission = app(Permission::class)->with('roles')->get()->first();

        $testPermission->assignRole('testRole');

        expect($testPermission->hasRole('testRole'))->toBeTrue();
    } catch (Exception $e) {
        $this->fail('Lazy loading detected in the givePermissionTo method: '.$e->getMessage());
    }
});

it('can be given a role on user when lazy loading is restricted', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();

    try {
        User::create(['email' => 'other@user.com']);
        $user = User::with('roles')->get()->first();
        $user->assignRole('testRole');

        expect($user->hasRole('testRole'))->toBeTrue();
    } catch (Exception $e) {
        $this->fail('Lazy loading detected in the givePermissionTo method: '.$e->getMessage());
    }
});

it('fires detach event when syncing roles', function () {
    Event::fake([RoleDetached::class, RoleAttached::class]);
    app('config')->set('permission.events_enabled', true);

    $this->testUser->assignRole('testRole', 'testRole2');

    app(Role::class)->create(['name' => 'testRole3']);

    $this->testUser->syncRoles('testRole3');

    expect($this->testUser->hasRole('testRole'))->toBeFalse();
    expect($this->testUser->hasRole('testRole2'))->toBeFalse();
    expect($this->testUser->hasRole('testRole3'))->toBeTrue();

    $removedRoleIds = app(Role::class)::whereIn('name', ['testRole', 'testRole2'])
        ->pluck($this->testUserRole->getKeyName())
        ->toArray();

    Event::assertDispatched(RoleDetached::class, function ($event) use ($removedRoleIds) {
        return $event->model instanceof User
            && ! $event->model->hasRole('testRole')
            && ! $event->model->hasRole('testRole2')
            && $event->rolesOrIds === $removedRoleIds;
    });

    $attachedRoleIds = app(Role::class)::whereIn('name', ['testRole3'])
        ->pluck($this->testUserRole->getKeyName())
        ->toArray();

    Event::assertDispatched(RoleAttached::class, function ($event) use ($attachedRoleIds) {
        return $event->model instanceof User
            && $event->model->hasRole('testRole3')
            && $event->rolesOrIds === $attachedRoleIds;
    });
});
