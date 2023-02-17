<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\SoftDeletingUser;
use Spatie\Permission\Tests\TestModels\User;

it('can determine that the user does not have a role', function () {
    expect($this->testUser->hasRole('testRole'))->toBeFalse();

    $role = app(Role::class)->findOrCreate('testRoleInWebGuard', 'web');

    expect($this->testUser->hasRole($role))->toBeFalse();

    $this->testUser->assignRole($role);
    expect($this->testUser->hasRole($role))->toBeTrue()
        ->and($this->testUser->hasRole($role->name))->toBeTrue()
        ->and($this->testUser->hasRole($role->name, $role->guard_name))->toBeTrue()
        ->and($this->testUser->hasRole([$role->name, 'fakeRole'], $role->guard_name))->toBeTrue()
        ->and($this->testUser->hasRole($role->getKey(), $role->guard_name))->toBeTrue()
        ->and($this->testUser->hasRole([$role->getKey(), 'fakeRole'], $role->guard_name))->toBeTrue()
        ->and($this->testUser->hasRole($role->name, 'fakeGuard'))->toBeFalse()
        ->and($this->testUser->hasRole([$role->name, 'fakeRole'], 'fakeGuard'))->toBeFalse()
        ->and($this->testUser->hasRole($role->getKey(), 'fakeGuard'))->toBeFalse()
        ->and($this->testUser->hasRole([$role->getKey(), 'fakeRole'], 'fakeGuard'))->toBeFalse();

    $role = app(Role::class)->findOrCreate('testRoleInWebGuard2', 'web');
    expect($this->testUser->hasRole($role))->toBeFalse();
});

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

    expect($roles->hasRole('testRole'))->toBeFalse()
        ->and($roles->hasRole('testRole2'))->toBeTrue();
});

it('can assign and remove a role on a permission', function () {
    $this->testUserPermission->assignRole('testRole');

    expect($this->testUserPermission->hasRole('testRole'))->toBeTrue();

    $this->testUserPermission->removeRole('testRole');

    expect($this->testUserPermission->hasRole('testRole'))->toBeFalse();
});

it('can assign a role using an object', function () {
    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasRole($this->testUserRole))->toBeTrue();
});

it('can assign a role using an id', function () {
    $this->testUser->assignRole($this->testUserRole->getKey());

    expect($this->testUser->hasRole($this->testUserRole))->toBeTrue();
});

it('can assign multiple roles at once', function () {
    $this->testUser->assignRole($this->testUserRole->getKey(), 'testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeTrue()
        ->and($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can assign multiple roles using an array', function () {
    $this->testUser->assignRole([$this->testUserRole->getKey(), 'testRole2']);

    expect($this->testUser->hasRole('testRole'))->toBeTrue()
        ->and($this->testUser->hasRole('testRole2'))->toBeTrue();
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
    $this->testUser->assignRole('evil-emperor');
})->throws(RoleDoesNotExist::class);

it('can only assign roles from the correct guard', function () {
    $this->testUser->assignRole('testAdminRole');
})->throws(RoleDoesNotExist::class);

it('throws an exception when assigning a role from a different guard', function () {
    $this->testUser->assignRole($this->testAdminRole);
})->throws(GuardDoesNotMatch::class);

it('ignores null roles when syncing', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->syncRoles('testRole2', null);

    expect($this->testUser->hasRole('testRole'))->toBeFalse()
        ->and($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can sync roles from a string', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->syncRoles('testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeFalse()
        ->and($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can sync roles from a string on a permission', function () {
    $this->testUserPermission->assignRole('testRole');

    $this->testUserPermission->syncRoles('testRole2');

    expect($this->testUserPermission->hasRole('testRole'))->toBeFalse()
        ->and($this->testUserPermission->hasRole('testRole2'))->toBeTrue();
});

it('can sync multiple roles', function () {
    $this->testUser->syncRoles('testRole', 'testRole2');

    expect($this->testUser->hasRole('testRole'))->toBeTrue()
        ->and($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('can sync multiple roles from an array', function () {
    $this->testUser->syncRoles(['testRole', 'testRole2']);

    expect($this->testUser->hasRole('testRole'))->toBeTrue()
        ->and($this->testUser->hasRole('testRole2'))->toBeTrue();
});

it('will remove all roles when an empty array is passed to sync roles', function () {
    $this->testUser->assignRole('testRole');

    $this->testUser->assignRole('testRole2');

    $this->testUser->syncRoles([]);

    expect($this->testUser->hasRole('testRole'))->toBeFalse()
        ->and($this->testUser->hasRole('testRole2'))->toBeFalse();
});

it('will sync roles to a model that is not persisted', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->syncRoles([$this->testUserRole]);
    $user->save();

    expect($user->hasRole($this->testUserRole))->toBeTrue();
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

    expect($user->fresh()->hasRole('testRole'))->toBeTrue()
        ->and($user->fresh()->hasRole('testRole2'))->toBeFalse()
        ->and($user2->fresh()->hasRole('testRole2'))->toBeTrue()
        ->and($user2->fresh()->hasRole('testRole'))->toBeFalse()
        ->and(count(DB::getQueryLog()))->toBe(4); //avoid unnecessary sync
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

    expect($user->fresh()->hasRole('testRole'))->toBeTrue()
        ->and($user->fresh()->hasRole('testRole2'))->toBeFalse()
        ->and($admin_user->fresh()->hasRole('testRole2'))->toBeTrue()
        ->and($admin_user->fresh()->hasRole('testRole'))->toBeFalse()
        ->and(count(DB::getQueryLog()))->toBe(4); //avoid unnecessary sync
});

it('throws an exception when syncing a role from another guard', function () {
    expect(function () {
        $this->testUser->syncRoles('testRole', 'testAdminRole');
    })->toThrow(RoleDoesNotExist::class)->and(function () {
        $this->testUser->syncRoles('testRole', $this->testAdminRole);
    })->toThrow(GuardDoesNotMatch::class);
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

it('can scope users using an array', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role([$this->testUserRole])->get();

    $scopedUsers2 = User::role(['testRole', 'testRole2'])->get();

    expect($scopedUsers1->count())->toEqual(1)
        ->and($scopedUsers2->count())->toEqual(2);
});

it('can scope users using an array of ids and names', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);

    $user1->assignRole($this->testUserRole);

    $user2->assignRole('testRole2');

    $roleName = $this->testUserRole->name;

    $otherRoleId = app(Role::class)->findByName('testRole2')->getKey();

    $scopedUsers = User::role([$roleName, $otherRoleId])->get();

    expect($scopedUsers->count())->toEqual(2);
});

it('can scope users using a collection', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role([$this->testUserRole])->get();
    $scopedUsers2 = User::role(collect(['testRole', 'testRole2']))->get();

    expect($scopedUsers1->count())->toEqual(1)
        ->and($scopedUsers2->count())->toEqual(2);
});

it('can scope users using an object', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole($this->testUserRole);
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role($this->testUserRole)->get();
    $scopedUsers2 = User::role([$this->testUserRole])->get();
    $scopedUsers3 = User::role(collect([$this->testUserRole]))->get();

    expect($scopedUsers1->count())->toEqual(1)
        ->and($scopedUsers2->count())->toEqual(1)
        ->and($scopedUsers3->count())->toEqual(1);
});

it('can scope against a specific guard', function () {
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user1->assignRole('testRole');
    $user2->assignRole('testRole2');

    $scopedUsers1 = User::role('testRole', 'web')->get();

    expect($scopedUsers1->count())->toEqual(1);

    $user3 = Admin::create(['email' => 'user1@test.com']);
    $user4 = Admin::create(['email' => 'user1@test.com']);
    $user5 = Admin::create(['email' => 'user2@test.com']);
    $testAdminRole2 = app(Role::class)->create(['name' => 'testAdminRole2', 'guard_name' => 'admin']);
    $user3->assignRole($this->testAdminRole);
    $user4->assignRole($this->testAdminRole);
    $user5->assignRole($testAdminRole2);
    $scopedUsers2 = Admin::role('testAdminRole', 'admin')->get();
    $scopedUsers3 = Admin::role('testAdminRole2', 'admin')->get();

    expect($scopedUsers2->count())->toEqual(2)
        ->and($scopedUsers3->count())->toEqual(1);
});

it('throws an exception when trying to scope a role from another guard', function () {
    $this->expectException(RoleDoesNotExist::class);

    User::role('testAdminRole')->get();

    $this->expectException(GuardDoesNotMatch::class);

    User::role($this->testAdminRole)->get();
});

it('throws an exception when trying to scope a non existing role', function () {
    User::role('role not defined')->get();
})->throws(RoleDoesNotExist::class);

it('can determine that a user has one of the given roles', function () {
    $roleModel = app(Role::class);

    $roleModel->create(['name' => 'second role']);

    expect($this->testUser->hasRole($roleModel->all()))->toBeFalse();

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasRole($roleModel->all()))->toBeTrue()
        ->and($this->testUser->hasAnyRole($roleModel->all()))->toBeTrue()
        ->and($this->testUser->hasAnyRole('testRole'))->toBeTrue()
        ->and($this->testUser->hasAnyRole('role does not exist'))->toBeFalse()
        ->and($this->testUser->hasAnyRole(['testRole']))->toBeTrue()
        ->and($this->testUser->hasAnyRole(['testRole', 'role does not exist']))->toBeTrue()
        ->and($this->testUser->hasAnyRole(['role does not exist']))->toBeFalse()
        ->and($this->testUser->hasAnyRole('testRole', 'role does not exist'))->toBeTrue();

});

it('can determine that a user has all of the given roles', function () {
    $roleModel = app(Role::class);

    expect($this->testUser->hasAllRoles($roleModel->first()))->toBeFalse()
        ->and($this->testUser->hasAllRoles('testRole'))->toBeFalse()
        ->and($this->testUser->hasAllRoles($roleModel->all()))->toBeFalse();

    $roleModel->create(['name' => 'second role']);

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasAllRoles('testRole'))->toBeTrue()
        ->and($this->testUser->hasAllRoles('testRole', 'web'))->toBeTrue()
        ->and($this->testUser->hasAllRoles('testRole', 'fakeGuard'))->toBeFalse()
        ->and($this->testUser->hasAllRoles(['testRole', 'second role']))->toBeFalse()
        ->and($this->testUser->hasAllRoles(['testRole', 'second role'], 'web'))->toBeFalse();

    $this->testUser->assignRole('second role');

    expect($this->testUser->hasAllRoles(['testRole', 'second role']))->toBeTrue()
        ->and($this->testUser->hasAllRoles(['testRole', 'second role'], 'web'))->toBeTrue()
        ->and($this->testUser->hasAllRoles(['testRole', 'second role'], 'fakeGuard'))->toBeFalse();
});

it('can determine that a user has exact all of the given roles', function () {
    $roleModel = app(Role::class);

    expect($this->testUser->hasExactRoles($roleModel->first()))->toBeFalse()
        ->and($this->testUser->hasExactRoles('testRole'))->toBeFalse()
        ->and($this->testUser->hasExactRoles($roleModel->all()))->toBeFalse();

    $roleModel->create(['name' => 'second role']);

    $this->testUser->assignRole($this->testUserRole);

    expect($this->testUser->hasExactRoles('testRole'))->toBeTrue()
        ->and($this->testUser->hasExactRoles('testRole', 'web'))->toBeTrue()
        ->and($this->testUser->hasExactRoles('testRole', 'fakeGuard'))->toBeFalse()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role']))->toBeFalse()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'))->toBeFalse();

    $this->testUser->assignRole('second role');

    expect($this->testUser->hasExactRoles(['testRole', 'second role']))->toBeTrue()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'))->toBeTrue()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role'], 'fakeGuard'))->toBeFalse();

    $roleModel->create(['name' => 'third role']);
    $this->testUser->assignRole('third role');

    expect($this->testUser->hasExactRoles(['testRole', 'second role']))->toBeFalse()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'))->toBeFalse()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role'], 'fakeGuard'))->toBeFalse()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role', 'third role']))->toBeTrue()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role', 'third role'], 'web'))->toBeTrue()
        ->and($this->testUser->hasExactRoles(['testRole', 'second role', 'third role'], 'fakeGuard'))->toBeFalse();
});

it('can determine that a user does not have a role from another guard', function () {
    expect($this->testUser->hasRole('testAdminRole'))->toBeFalse()
        ->and($this->testUser->hasRole($this->testAdminRole))->toBeFalse();

    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasAnyRole(['testRole', 'testAdminRole']))->toBeTrue()
        ->and($this->testUser->hasAnyRole('testAdminRole', $this->testAdminRole))->toBeFalse();

});

it('can check against any multiple roles using multiple arguments', function () {
    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasAnyRole($this->testAdminRole, ['testRole'], 'This Role Does Not Even Exist'))->toBeTrue();
});

it('returns false instead of an exception when checking against any undefined roles using multiple arguments', function () {
    expect($this->testUser->hasAnyRole('This Role Does Not Even Exist', $this->testAdminRole))->toBeFalse();
});

it('can retrieve role names', function () {
    $this->testUser->assignRole('testRole', 'testRole2');

    expect($this->testUser->getRoleNames()->sort()->values())->toEqual(collect(['testRole', 'testRole2']));
});

it('does not detach roles when soft deleting', function () {
    $user = SoftDeletingUser::create(['email' => 'test@example.com']);
    $user->assignRole('testRole');
    $user->delete();

    $user = SoftDeletingUser::withTrashed()->find($user->id);

    expect($user->hasRole('testRole'))->toBeTrue();
});
