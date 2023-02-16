<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\User;

function getWriter(): User
{
    test()->testUser->assignRole('writer');

    return test()->testUser;
}

function getMember(): User
{
    test()->testUser->assignRole('member');

    return test()->testUser;
}

function getSuperAdmin(): Admin
{
    test()->testAdmin->assignRole('super-admin');

    return test()->testAdmin;
}

function renderView($view, $parameters): string
{
    Artisan::call('view:clear');

    if (is_string($view)) {
        $view = view($view)->with($parameters);
    }

    return trim((string) ($view));
}

beforeEach(function () {
    $roleModel = app(Role::class);

    $roleModel->create(['name' => 'member']);
    $roleModel->create(['name' => 'writer']);
    $roleModel->create(['name' => 'intern']);
    $roleModel->create(['name' => 'super-admin', 'guard_name' => 'admin']);
    $roleModel->create(['name' => 'moderator', 'guard_name' => 'admin']);
});

it('all blade directives will evaluate false when there is nobody logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = [$role];
    $elserole = 'na';

    expect(renderView('can', ['permission' => $permission]))->toEqual('does not have permission');
    expect(renderView('role', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does not have all of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    expect(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does not have any of the given roles');
});

it('all blade directives will evaluate false when somebody without roles or permissions is logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = 'writer';
    $elserole = 'na';

    auth()->setUser($this->testUser);

    expect(renderView('can', ['permission' => $permission]))->toEqual('does not have permission');
    expect(renderView('role', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
});

it('all blade directives will evaluate false when somebody with another guard is logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = 'writer';
    $elserole = 'na';

    auth('admin')->setUser($this->testAdmin);

    expect(renderView('can', compact('permission')))->toEqual('does not have permission');
    expect(renderView('role', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
});

it('the can directive will evaluate true when the logged in user has the permission', function () {
    $user = getWriter();

    $user->givePermissionTo('edit-articles');

    auth()->setUser($user);

    expect(renderView('can', ['permission' => 'edit-articles']))->toEqual('has permission');
});

it('the role directive will evaluate true when the logged in user has the role', function () {
    auth()->setUser(getWriter());

    expect(renderView('role', ['role' => 'writer', 'elserole' => 'na']))->toEqual('has role');
});

it('the elserole directive will evaluate true when the logged in user has the role', function () {
    auth()->setUser(getMember());

    expect(renderView('role', ['role' => 'writer', 'elserole' => 'member']))->toEqual('has else role');
});

it('the role directive will evaluate true when the logged in user has the role for the given guard', function () {
    auth('admin')->setUser(getSuperAdmin());

    expect(renderView('guardRole', ['role' => 'super-admin', 'guard' => 'admin']))->toEqual('has role for guard');
});

it('the hasrole directive will evaluate true when the logged in user has the role', function () {
    auth()->setUser(getWriter());

    expect(renderView('hasRole', ['role' => 'writer']))->toEqual('has role');
});

it('the hasrole directive will evaluate true when the logged in user has the role for the given guard', function () {
    auth('admin')->setUser(getSuperAdmin());

    expect(renderView('guardHasRole', ['role' => 'super-admin', 'guard' => 'admin']))->toEqual('has role');
});

it('the unlessrole directive will evaluate true when the logged in user does not have the role', function () {
    auth()->setUser(getWriter());

    expect(renderView('unlessrole', ['role' => 'another']))->toEqual('does not have role');
});

it('the unlessrole directive will evaluate true when the logged in user does not have the role for the given guard', function () {
    auth('admin')->setUser(getSuperAdmin());

    expect(renderView('guardunlessrole', ['role' => 'another', 'guard' => 'admin']))->toEqual('does not have role');
    expect(renderView('guardunlessrole', ['role' => 'super-admin', 'guard' => 'web']))->toEqual('does not have role');
});

it('the hasanyrole directive will evaluate false when the logged in user does not have any of the required roles', function () {
    $roles = ['writer', 'intern'];

    auth()->setUser(getMember());

    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    expect(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does not have any of the given roles');
});

it('the hasanyrole directive will evaluate true when the logged in user does have some of the required roles', function () {
    $roles = ['member', 'writer', 'intern'];

    auth()->setUser(getMember());

    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does have some of the roles');
    expect(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does have some of the roles');
});

it('the hasanyrole directive will evaluate true when the logged in user does have some of the required roles for the given guard', function () {
    $roles = ['super-admin', 'moderator'];
    $guard = 'admin';

    auth('admin')->setUser(getSuperAdmin());

    expect(renderView('guardHasAnyRole', compact('roles', 'guard')))->toEqual('does have some of the roles');
});

it('the hasanyrole directive will evaluate true when the logged in user does have some of the required roles in pipe', function () {
    $guard = 'admin';

    auth('admin')->setUser(getSuperAdmin());

    expect(renderView('guardHasAnyRolePipe', compact('guard')))->toEqual('does have some of the roles');
});

it('the hasanyrole directive will evaluate false when the logged in user doesnt have some of the required roles in pipe', function () {
    $guard = '';

    auth('admin')->setUser(getMember());

    expect(renderView('guardHasAnyRolePipe', compact('guard')))->toEqual('does not have any of the given roles');
});

it('the hasallroles directive will evaluate false when the logged in user does not have all required roles', function () {
    $roles = ['member', 'writer'];

    auth()->setUser(getMember());

    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does not have all of the given roles');
});

it('the hasallroles directive will evaluate true when the logged in user does have all required roles', function () {
    $roles = ['member', 'writer'];

    $user = getMember();

    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does have all of the given roles');
    expect(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does have all of the given roles');
});

it('the hasallroles directive will evaluate true when the logged in user does have all required roles for the given guard', function () {
    $roles = ['super-admin', 'moderator'];
    $guard = 'admin';

    $admin = getSuperAdmin();

    $admin->assignRole('moderator');

    auth('admin')->setUser($admin);

    expect(renderView('guardHasAllRoles', compact('roles', 'guard')))->toEqual('does have all of the given roles');
});

it('the hasallroles directive will evaluate true when the logged in user does have all required roles in pipe', function () {
    $guard = 'admin';

    $admin = getSuperAdmin();

    $admin->assignRole('moderator');

    auth('admin')->setUser($admin);

    expect(renderView('guardHasAllRolesPipe', compact('guard')))->toEqual('does have all of the given roles');
});

it('the hasallroles directive will evaluate false when the logged in user doesnt have all required roles in pipe', function () {
    $guard = '';
    $user = getMember();

    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('guardHasAllRolesPipe', compact('guard')))->toEqual('does not have all of the given roles');
});

it('the hasallroles directive will evaluate true when the logged in user does have all required roles in array', function () {
    $guard = 'admin';

    $admin = getSuperAdmin();

    $admin->assignRole('moderator');

    auth('admin')->setUser($admin);

    expect(renderView('guardHasAllRolesArray', compact('guard')))->toEqual('does have all of the given roles');
});

it('the hasallroles directive will evaluate false when the logged in user doesnt have all required roles in array', function () {
    $guard = '';
    $user = getMember();

    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('guardHasAllRolesArray', compact('guard')))->toEqual('does not have all of the given roles');
});
