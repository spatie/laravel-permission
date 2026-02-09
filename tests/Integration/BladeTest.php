<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Contracts\Role;

function renderView(string $view, array $parameters): string
{
    Artisan::call('view:clear');

    return trim((string) view($view)->with($parameters));
}

beforeEach(function () {
    $roleModel = app(Role::class);

    $roleModel->create(['name' => 'member']);
    $roleModel->create(['name' => 'writer']);
    $roleModel->create(['name' => 'intern']);
    $roleModel->create(['name' => 'super-admin', 'guard_name' => 'admin']);
    $roleModel->create(['name' => 'moderator', 'guard_name' => 'admin']);

    $this->getWriter = function () {
        $this->testUser->assignRole('writer');

        return $this->testUser;
    };

    $this->getMember = function () {
        $this->testUser->assignRole('member');

        return $this->testUser;
    };

    $this->getSuperAdmin = function () {
        $this->testAdmin->assignRole('super-admin');

        return $this->testAdmin;
    };
});

it('evaluates all blade directives as false when there is nobody logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = [$role];
    $elserole = 'na';
    $elsepermission = 'na';

    expect(renderView('can', ['permission' => $permission]))->toEqual('does not have permission');
    expect(renderView('haspermission', compact('permission', 'elsepermission')))->toEqual('does not have permission');
    expect(renderView('role', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does not have all of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    expect(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does not have any of the given roles');
});

it('evaluates all blade directives as false when somebody without roles or permissions is logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = 'writer';
    $elserole = 'na';
    $elsepermission = 'na';

    auth()->setUser($this->testUser);

    expect(renderView('can', compact('permission')))->toEqual('does not have permission');
    expect(renderView('haspermission', compact('permission', 'elsepermission')))->toEqual('does not have permission');
    expect(renderView('role', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
});

it('evaluates all blade directives as false when somebody with another guard is logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = 'writer';
    $elserole = 'na';
    $elsepermission = 'na';

    auth('admin')->setUser($this->testAdmin);

    expect(renderView('can', compact('permission')))->toEqual('does not have permission');
    expect(renderView('haspermission', compact('permission', 'elsepermission')))->toEqual('does not have permission');
    expect(renderView('role', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role');
    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
});

it('accepts a guard name in the can directive', function () {
    $user = ($this->getWriter)();
    $user->givePermissionTo('edit-articles');
    auth()->setUser($user);

    $permission = 'edit-articles';
    $guard = 'web';
    expect(renderView('can', compact('permission', 'guard')))->toEqual('has permission');
    $guard = 'admin';
    expect(renderView('can', compact('permission', 'guard')))->toEqual('does not have permission');

    auth()->logout();

    // log in as the Admin with the permission-via-role
    $this->testAdmin->givePermissionTo($this->testAdminPermission);
    $user = $this->testAdmin;
    auth()->setUser($user);

    $permission = 'edit-articles';
    $guard = 'web';
    expect(renderView('can', compact('permission', 'guard')))->toEqual('does not have permission');

    $permission = 'admin-permission';
    $guard = 'admin';
    expect($this->testAdmin->checkPermissionTo($permission, $guard))->toBeTrue();
    expect(renderView('can', compact('permission', 'guard')))->toEqual('has permission');
});

it('evaluates the can directive as true when the logged in user has the permission', function () {
    $user = ($this->getWriter)();
    $user->givePermissionTo('edit-articles');

    auth()->setUser($user);

    expect(renderView('can', ['permission' => 'edit-articles']))->toEqual('has permission');
});

it('evaluates the haspermission directive as true when the logged in user has the permission', function () {
    $user = ($this->getWriter)();

    $permission = 'edit-articles';
    $user->givePermissionTo('edit-articles');

    auth()->setUser($user);

    expect(renderView('haspermission', compact('permission')))->toEqual('has permission');

    $guard = 'admin';
    $elsepermission = 'na';
    expect(renderView('haspermission', compact('permission', 'elsepermission', 'guard')))->toEqual('does not have permission');

    $this->testAdminRole->givePermissionTo($this->testAdminPermission);
    $this->testAdmin->assignRole($this->testAdminRole);
    auth('admin')->setUser($this->testAdmin);
    $guard = 'admin';
    $permission = 'admin-permission';
    expect(renderView('haspermission', compact('permission', 'guard', 'elsepermission')))->toEqual('has permission');
});

it('evaluates the role directive as true when the logged in user has the role', function () {
    auth()->setUser(($this->getWriter)());

    expect(renderView('role', ['role' => 'writer', 'elserole' => 'na']))->toEqual('has role');
});

it('evaluates the elserole directive as true when the logged in user has the role', function () {
    auth()->setUser(($this->getMember)());

    expect(renderView('role', ['role' => 'writer', 'elserole' => 'member']))->toEqual('has else role');
});

it('evaluates the role directive as true when the logged in user has the role for the given guard', function () {
    auth('admin')->setUser(($this->getSuperAdmin)());

    expect(renderView('guardRole', ['role' => 'super-admin', 'guard' => 'admin']))->toEqual('has role for guard');
});

it('evaluates the hasrole directive as true when the logged in user has the role', function () {
    auth()->setUser(($this->getWriter)());

    expect(renderView('hasRole', ['role' => 'writer']))->toEqual('has role');
});

it('evaluates the hasrole directive as true when the logged in user has the role for the given guard', function () {
    auth('admin')->setUser(($this->getSuperAdmin)());

    expect(renderView('guardHasRole', ['role' => 'super-admin', 'guard' => 'admin']))->toEqual('has role');
});

it('evaluates the unlessrole directive as true when the logged in user does not have the role', function () {
    auth()->setUser(($this->getWriter)());

    expect(renderView('unlessrole', ['role' => 'another']))->toEqual('does not have role');
});

it('evaluates the unlessrole directive as true when the logged in user does not have the role for the given guard', function () {
    auth('admin')->setUser(($this->getSuperAdmin)());

    expect(renderView('guardunlessrole', ['role' => 'another', 'guard' => 'admin']))->toEqual('does not have role');
    expect(renderView('guardunlessrole', ['role' => 'super-admin', 'guard' => 'web']))->toEqual('does not have role');
});

it('evaluates the hasanyrole directive as false when the logged in user does not have any of the required roles', function () {
    $roles = ['writer', 'intern'];

    auth()->setUser(($this->getMember)());

    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    expect(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does not have any of the given roles');
});

it('evaluates the hasanyrole directive as true when the logged in user does have some of the required roles', function () {
    $roles = ['member', 'writer', 'intern'];

    auth()->setUser(($this->getMember)());

    expect(renderView('hasAnyRole', compact('roles')))->toEqual('does have some of the roles');
    expect(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does have some of the roles');
});

it('evaluates the hasanyrole directive as true when the logged in user does have some of the required roles for the given guard', function () {
    $roles = ['super-admin', 'moderator'];
    $guard = 'admin';

    auth('admin')->setUser(($this->getSuperAdmin)());

    expect(renderView('guardHasAnyRole', compact('roles', 'guard')))->toEqual('does have some of the roles');
});

it('evaluates the hasanyrole directive as true when the logged in user does have some of the required roles in pipe', function () {
    $guard = 'admin';

    auth('admin')->setUser(($this->getSuperAdmin)());

    expect(renderView('guardHasAnyRolePipe', compact('guard')))->toEqual('does have some of the roles');
});

it('evaluates the hasanyrole directive as false when the logged in user doesnt have some of the required roles in pipe', function () {
    $guard = '';

    auth('admin')->setUser(($this->getMember)());

    expect(renderView('guardHasAnyRolePipe', compact('guard')))->toEqual('does not have any of the given roles');
});

it('evaluates the hasallroles directive as false when the logged in user does not have all required roles', function () {
    $roles = ['member', 'writer'];

    auth()->setUser(($this->getMember)());

    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles');
    expect(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does not have all of the given roles');
});

it('evaluates the hasallroles directive as true when the logged in user does have all required roles', function () {
    $roles = ['member', 'writer'];

    $user = ($this->getMember)();
    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('hasAllRoles', compact('roles')))->toEqual('does have all of the given roles');
    expect(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does have all of the given roles');
});

it('evaluates the hasallroles directive as true when the logged in user does have all required roles for the given guard', function () {
    $roles = ['super-admin', 'moderator'];
    $guard = 'admin';

    $admin = ($this->getSuperAdmin)();
    $admin->assignRole('moderator');

    auth('admin')->setUser($admin);

    expect(renderView('guardHasAllRoles', compact('roles', 'guard')))->toEqual('does have all of the given roles');
});

it('evaluates the hasallroles directive as true when the logged in user does have all required roles in pipe', function () {
    $guard = 'admin';

    $admin = ($this->getSuperAdmin)();
    $admin->assignRole('moderator');

    auth('admin')->setUser($admin);

    expect(renderView('guardHasAllRolesPipe', compact('guard')))->toEqual('does have all of the given roles');
});

it('evaluates the hasallroles directive as false when the logged in user doesnt have all required roles in pipe', function () {
    $guard = '';
    $user = ($this->getMember)();
    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('guardHasAllRolesPipe', compact('guard')))->toEqual('does not have all of the given roles');
});

it('evaluates the hasallroles directive as true when the logged in user does have all required roles in array', function () {
    $guard = 'admin';

    $admin = ($this->getSuperAdmin)();
    $admin->assignRole('moderator');

    auth('admin')->setUser($admin);

    expect(renderView('guardHasAllRolesArray', compact('guard')))->toEqual('does have all of the given roles');
});

it('evaluates the hasallroles directive as false when the logged in user doesnt have all required roles in array', function () {
    $guard = '';
    $user = ($this->getMember)();
    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('guardHasAllRolesArray', compact('guard')))->toEqual('does not have all of the given roles');
});
