<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;

beforeEach(function () {
    $roleModel = app(Role::class);

    $roleModel->create(['name' => 'member']);
    $roleModel->create(['name' => 'writer']);
    $roleModel->create(['name' => 'intern']);
    $roleModel->create(['name' => 'super-admin', 'guard_name' => 'admin']);
    $roleModel->create(['name' => 'moderator', 'guard_name' => 'admin']);
});

test('all blade directives will evaluate false when there is nobody logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = [$role];
    $elserole = 'na';

    expect(renderView('can', ['permission' => $permission]))->toEqual('does not have permission')
        ->and(renderView('role', compact('role', 'elserole')))->toEqual('does not have role')
        ->and(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role')
        ->and(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles')
        ->and(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))->toEqual('does not have all of the given roles')
        ->and(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles')
        ->and(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))->toEqual('does not have any of the given roles');
});

test(
    'all blade directives will evaluate false when somebody without roles or permissions is logged in',
    function () {
        $permission = 'edit-articles';
        $role = 'writer';
        $roles = 'writer';
        $elserole = 'na';

        auth()->setUser($this->testUser);

        expect(renderView('can', ['permission' => $permission]))->toEqual('does not have permission')
            ->and(renderView('role', compact('role', 'elserole')))->toEqual('does not have role')
            ->and(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role')
            ->and(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles')
            ->and(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
    }
);

test('all blade directives will evaluate false when somebody with another guard is logged in', function () {
    $permission = 'edit-articles';
    $role = 'writer';
    $roles = 'writer';
    $elserole = 'na';

    auth('admin')->setUser($this->testAdmin);

    expect(renderView('can', compact('permission')))->toEqual('does not have permission')
        ->and(renderView('role', compact('role', 'elserole')))->toEqual('does not have role')
        ->and(renderView('hasRole', compact('role', 'elserole')))->toEqual('does not have role')
        ->and(renderView('hasAllRoles', compact('roles')))->toEqual('does not have all of the given roles')
        ->and(renderView('hasAnyRole', compact('roles')))->toEqual('does not have any of the given roles');
});

test('the "can" directive will evaluate true when the logged in user has the permission', function () {
    $user = getWriter();

    $user->givePermissionTo('edit-articles');

    auth()->setUser($user);

    expect(renderView('can', ['permission' => 'edit-articles']))->toEqual('has permission');
});

test('the "role" directive will evaluate true when the logged in user has the role')
    ->tap(fn () => auth()->setUser(getWriter()))
    ->expect(fn () => renderView('role', ['role' => 'writer', 'elserole' => 'na']))
    ->toEqual('has role');

test('the "elserole" directive will evaluate true when the logged in user has the role')
    ->tap(fn () => auth()->setUser(getMember()))
    ->expect(fn () => renderView('role', ['role' => 'writer', 'elserole' => 'member']))
    ->toEqual('has else role');

test('the "role" directive will evaluate true when the logged in user has the role for the given guard')
    ->tap(fn () =>  auth('admin')->setUser(getSuperAdmin()))
    ->expect(fn () => renderView('guardRole', ['role' => 'super-admin', 'guard' => 'admin']))
    ->toEqual('has role for guard');

test('the "hasrole" directive will evaluate true when the logged in user has the role')
    ->tap(fn () => auth()->setUser(getWriter()))
    ->expect(fn () => renderView('hasRole', ['role' => 'writer']))
    ->toEqual('has role');

test('the "hasrole" directive will evaluate true when the logged in user has the role for the given guard')
    ->tap(fn () => auth('admin')->setUser(getSuperAdmin()))
    ->expect(fn () => renderView('guardHasRole', ['role' => 'super-admin', 'guard' => 'admin']))
    ->toEqual('has role');


test('the "unlessrole" directive will evaluate true when the logged in user does not have the role')
    ->tap(fn () => auth()->setUser(getWriter()))
    ->expect(fn () => renderView('unlessrole', ['role' => 'another']))
    ->toEqual('does not have role');

test(
    'the "unlessrole" directive will evaluate true when the logged in user does not have the role for the given guard'
)
    ->tap(fn () => auth('admin')->setUser(getSuperAdmin()))
    ->expect(fn () => renderView('guardunlessrole', ['role' => 'another', 'guard' => 'admin']))
    ->toEqual('does not have role')
    ->expect(fn () => renderView('guardunlessrole', ['role' => 'super-admin', 'guard' => 'web']))
    ->toEqual('does not have role');

test(
    'the "hasanyrole" directive will evaluate false when the logged in user does not have any of the required roles',
    function () {
        $roles = ['writer', 'intern'];

        auth()->setUser(getMember());

        expect(renderView('hasAnyRole', compact('roles')))
            ->toEqual('does not have any of the given roles')
            ->and(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))
            ->toEqual('does not have any of the given roles');
    }
);

test(
    'the "hasanyrole" directive will evaluate true when the logged in user does have some of the required roles',
    function () {
        $roles = ['member', 'writer', 'intern'];

        auth()->setUser(getMember());

        expect(renderView('hasAnyRole', compact('roles')))
            ->toEqual('does have some of the roles')
            ->and(renderView('hasAnyRole', ['roles' => implode('|', $roles)]))
            ->toEqual('does have some of the roles');;
    }
);

test(
    'the "hasanyrole" directive will evaluate true when the logged in user does have some of the required roles for the given guard',
    function () {
        $roles = ['super-admin', 'moderator'];
        $guard = 'admin';

        auth('admin')->setUser(getSuperAdmin());

        expect(renderView('guardHasAnyRole', compact('roles', 'guard')))
            ->toEqual('does have some of the roles');
    }
);

test('the "hasanyrole" directive will evaluate `true` when the logged in user does have some of the required roles in pipes', function () {
    $guard = 'admin';

    auth('admin')->setUser(getSuperAdmin());

    $this->assertEquals('does have some of the roles', renderView('guardHasAnyRolePipe', compact('guard')));
});

test(
    'the "hasanyrole" directive will evaluate `false` when the logged in user does not have some of the required roles in pipes',
    function () {
        $guard = '';

        auth('admin')->setUser(getMember());

        $this->assertEquals('does not have any of the given roles', renderView('guardHasAnyRolePipe', compact('guard')));
    }
);

test(
    'the "hasallroles" directive will evaluate `false` when the logged in user does not have all required roles',
    function () {
        $roles = ['member', 'writer'];

        auth()->setUser(getMember());

        expect(renderView('hasAllRoles', compact('roles')))
            ->toEqual('does not have all of the given roles')
            ->and(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))
            ->toEqual('does not have all of the given roles');
    }
);

test('the "hasallroles" directive will evaluate `true` when the logged in user does have all required roles', function () {
    $roles = ['member', 'writer'];

    $user = getMember();

    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('hasAllRoles', compact('roles')))
        ->toEqual('does have all of the given roles')
        ->and(renderView('hasAllRoles', ['roles' => implode('|', $roles)]))
        ->toEqual('does have all of the given roles');
});

test(
    'the "hasallroles" directive will evaluate `true` when the logged in user does have all required roles for the given guard',
    function () {
        $roles = ['super-admin', 'moderator'];
        $guard = 'admin';

        $admin = getSuperAdmin();

        $admin->assignRole('moderator');

        auth('admin')->setUser($admin);

        expect(renderView('guardHasAllRoles', compact('roles', 'guard')))
            ->toEqual('does have all of the given roles');
    }
);

test(
    'the "hasallroles" directive will evaluate true when the logged in user does have all required roles in pipe',
    function () {
        $guard = 'admin';

        $admin = getSuperAdmin();

        $admin->assignRole('moderator');

        auth('admin')->setUser($admin);

        expect(renderView('guardHasAllRolesPipe', compact('guard')))
            ->toEqual('does have all of the given roles');
    }
);

test('the "hasallroles" directive will evaluate `false` when the logged in user does not have all required roles in pipe', function () {
    $guard = '';
    $user = getMember();

    $user->assignRole('writer');

    auth()->setUser($user);

    expect(renderView('guardHasAllRolesPipe', compact('guard')))
        ->toEqual('does not have all of the given roles');
});

test(
    'the "hasallroles" directive will evaluate `true` when the logged in user does have all required roles in array',
    function () {
        $guard = 'admin';

        $admin = getSuperAdmin();

        $admin->assignRole('moderator');

        auth('admin')->setUser($admin);

        expect(renderView('guardHasAllRolesArray', compact('guard')))
            ->toEqual('does have all of the given roles');
    }
);

test(
    'the "hasallroles" directive will evaluate `false` when the logged in user does not have all required roles in array',
    function () {
        $guard = '';
        $user = getMember();

        $user->assignRole('writer');

        auth()->setUser($user);

        expect(renderView('guardHasAllRolesArray', compact('guard')))
            ->toEqual('does not have all of the given roles');
    }
);
