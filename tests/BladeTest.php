<?php

namespace Spatie\Permission\Tests;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Contracts\Role;

class BladeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $roleModel = app(Role::class);

        $roleModel->create(['name' => 'member']);
        $roleModel->create(['name' => 'writer']);
        $roleModel->create(['name' => 'intern']);
        $roleModel->create(['name' => 'super-admin', 'guard_name' => 'admin']);
        $roleModel->create(['name' => 'moderator', 'guard_name' => 'admin']);
    }

    /** @test */
    #[Test]
    public function all_blade_directives_will_evaluate_false_when_there_is_nobody_logged_in()
    {
        $permission = 'edit-articles';
        $role = 'writer';
        $roles = [$role];
        $elserole = 'na';
        $elsepermission = 'na';

        $this->assertEquals('does not have permission', $this->renderView('can', ['permission' => $permission]));
        $this->assertEquals('does not have permission', $this->renderView('haspermission', compact('permission', 'elsepermission')));
        $this->assertEquals('does not have role', $this->renderView('role', compact('role', 'elserole')));
        $this->assertEquals('does not have role', $this->renderView('hasRole', compact('role', 'elserole')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', ['roles' => implode('|', $roles)]));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', ['roles' => implode('|', $roles)]));
    }

    /** @test */
    #[Test]
    public function all_blade_directives_will_evaluate_false_when_somebody_without_roles_or_permissions_is_logged_in()
    {
        $permission = 'edit-articles';
        $role = 'writer';
        $roles = 'writer';
        $elserole = 'na';
        $elsepermission = 'na';

        auth()->setUser($this->testUser);

        $this->assertEquals('does not have permission', $this->renderView('can', compact('permission')));
        $this->assertEquals('does not have permission', $this->renderView('haspermission', compact('permission', 'elsepermission')));
        $this->assertEquals('does not have role', $this->renderView('role', compact('role', 'elserole')));
        $this->assertEquals('does not have role', $this->renderView('hasRole', compact('role', 'elserole')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
    }

    /** @test */
    #[Test]
    public function all_blade_directives_will_evaluate_false_when_somebody_with_another_guard_is_logged_in()
    {
        $permission = 'edit-articles';
        $role = 'writer';
        $roles = 'writer';
        $elserole = 'na';
        $elsepermission = 'na';

        auth('admin')->setUser($this->testAdmin);

        $this->assertEquals('does not have permission', $this->renderView('can', compact('permission')));
        $this->assertEquals('does not have permission', $this->renderView('haspermission', compact('permission', 'elsepermission')));
        $this->assertEquals('does not have role', $this->renderView('role', compact('role', 'elserole')));
        $this->assertEquals('does not have role', $this->renderView('hasRole', compact('role', 'elserole')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
    }

    /** @test */
    #[Test]
    public function the_can_directive_can_accept_a_guard_name()
    {
        $user = $this->getWriter();
        $user->givePermissionTo('edit-articles');
        auth()->setUser($user);

        $permission = 'edit-articles';
        $guard = 'web';
        $this->assertEquals('has permission', $this->renderView('can', compact('permission', 'guard')));
        $guard = 'admin';
        $this->assertEquals('does not have permission', $this->renderView('can', compact('permission', 'guard')));

        auth()->logout();

        // log in as the Admin with the permission-via-role
        $this->testAdmin->givePermissionTo($this->testAdminPermission);
        $user = $this->testAdmin;
        auth()->setUser($user);

        $permission = 'edit-articles';
        $guard = 'web';
        $this->assertEquals('does not have permission', $this->renderView('can', compact('permission', 'guard')));

        $permission = 'admin-permission';
        $guard = 'admin';
        $this->assertTrue($this->testAdmin->checkPermissionTo($permission, $guard));
        $this->assertEquals('has permission', $this->renderView('can', compact('permission', 'guard')));
    }

    /** @test */
    #[Test]
    public function the_can_directive_will_evaluate_true_when_the_logged_in_user_has_the_permission()
    {
        $user = $this->getWriter();

        $user->givePermissionTo('edit-articles');

        auth()->setUser($user);

        $this->assertEquals('has permission', $this->renderView('can', ['permission' => 'edit-articles']));
    }

    /** @test */
    #[Test]
    public function the_haspermission_directive_will_evaluate_true_when_the_logged_in_user_has_the_permission()
    {
        $user = $this->getWriter();

        $permission = 'edit-articles';
        $user->givePermissionTo('edit-articles');

        auth()->setUser($user);

        $this->assertEquals('has permission', $this->renderView('haspermission', compact('permission')));

        $guard = 'admin';
        $elsepermission = 'na';
        $this->assertEquals('does not have permission', $this->renderView('haspermission', compact('permission', 'elsepermission', 'guard')));

        $this->testAdminRole->givePermissionTo($this->testAdminPermission);
        $this->testAdmin->assignRole($this->testAdminRole);
        auth('admin')->setUser($this->testAdmin);
        $guard = 'admin';
        $permission = 'admin-permission';
        $this->assertEquals('has permission', $this->renderView('haspermission', compact('permission', 'guard', 'elsepermission')));
    }

    /** @test */
    #[Test]
    public function the_role_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->setUser($this->getWriter());

        $this->assertEquals('has role', $this->renderView('role', ['role' => 'writer', 'elserole' => 'na']));
    }

    /** @test */
    #[Test]
    public function the_elserole_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->setUser($this->getMember());

        $this->assertEquals('has else role', $this->renderView('role', ['role' => 'writer', 'elserole' => 'member']));
    }

    /** @test */
    #[Test]
    public function the_role_directive_will_evaluate_true_when_the_logged_in_user_has_the_role_for_the_given_guard()
    {
        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('has role for guard', $this->renderView('guardRole', ['role' => 'super-admin', 'guard' => 'admin']));
    }

    /** @test */
    #[Test]
    public function the_hasrole_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->setUser($this->getWriter());

        $this->assertEquals('has role', $this->renderView('hasRole', ['role' => 'writer']));
    }

    /** @test */
    #[Test]
    public function the_hasrole_directive_will_evaluate_true_when_the_logged_in_user_has_the_role_for_the_given_guard()
    {
        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('has role', $this->renderView('guardHasRole', ['role' => 'super-admin', 'guard' => 'admin']));
    }

    /** @test */
    #[Test]
    public function the_unlessrole_directive_will_evaluate_true_when_the_logged_in_user_does_not_have_the_role()
    {
        auth()->setUser($this->getWriter());

        $this->assertEquals('does not have role', $this->renderView('unlessrole', ['role' => 'another']));
    }

    /** @test */
    #[Test]
    public function the_unlessrole_directive_will_evaluate_true_when_the_logged_in_user_does_not_have_the_role_for_the_given_guard()
    {
        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('does not have role', $this->renderView('guardunlessrole', ['role' => 'another', 'guard' => 'admin']));
        $this->assertEquals('does not have role', $this->renderView('guardunlessrole', ['role' => 'super-admin', 'guard' => 'web']));
    }

    /** @test */
    #[Test]
    public function the_hasanyrole_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_any_of_the_required_roles()
    {
        $roles = ['writer', 'intern'];

        auth()->setUser($this->getMember());

        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', ['roles' => implode('|', $roles)]));
    }

    /** @test */
    #[Test]
    public function the_hasanyrole_directive_will_evaluate_true_when_the_logged_in_user_does_have_some_of_the_required_roles()
    {
        $roles = ['member', 'writer', 'intern'];

        auth()->setUser($this->getMember());

        $this->assertEquals('does have some of the roles', $this->renderView('hasAnyRole', compact('roles')));
        $this->assertEquals('does have some of the roles', $this->renderView('hasAnyRole', ['roles' => implode('|', $roles)]));
    }

    /** @test */
    #[Test]
    public function the_hasanyrole_directive_will_evaluate_true_when_the_logged_in_user_does_have_some_of_the_required_roles_for_the_given_guard()
    {
        $roles = ['super-admin', 'moderator'];
        $guard = 'admin';

        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('does have some of the roles', $this->renderView('guardHasAnyRole', compact('roles', 'guard')));
    }

    /** @test */
    #[Test]
    public function the_hasanyrole_directive_will_evaluate_true_when_the_logged_in_user_does_have_some_of_the_required_roles_in_pipe()
    {
        $guard = 'admin';

        auth('admin')->setUser($this->getSuperAdmin());

        $this->assertEquals('does have some of the roles', $this->renderView('guardHasAnyRolePipe', compact('guard')));
    }

    /** @test */
    #[Test]
    public function the_hasanyrole_directive_will_evaluate_false_when_the_logged_in_user_doesnt_have_some_of_the_required_roles_in_pipe()
    {
        $guard = '';

        auth('admin')->setUser($this->getMember());

        $this->assertEquals('does not have any of the given roles', $this->renderView('guardHasAnyRolePipe', compact('guard')));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_all_required_roles()
    {
        $roles = ['member', 'writer'];

        auth()->setUser($this->getMember());

        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', ['roles' => implode('|', $roles)]));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_roles()
    {
        $roles = ['member', 'writer'];

        $user = $this->getMember();

        $user->assignRole('writer');

        auth()->setUser($user);

        $this->assertEquals('does have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does have all of the given roles', $this->renderView('hasAllRoles', ['roles' => implode('|', $roles)]));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_roles_for_the_given_guard()
    {
        $roles = ['super-admin', 'moderator'];
        $guard = 'admin';

        $admin = $this->getSuperAdmin();

        $admin->assignRole('moderator');

        auth('admin')->setUser($admin);

        $this->assertEquals('does have all of the given roles', $this->renderView('guardHasAllRoles', compact('roles', 'guard')));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_roles_in_pipe()
    {
        $guard = 'admin';

        $admin = $this->getSuperAdmin();

        $admin->assignRole('moderator');

        auth('admin')->setUser($admin);

        $this->assertEquals('does have all of the given roles', $this->renderView('guardHasAllRolesPipe', compact('guard')));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_false_when_the_logged_in_user_doesnt_have_all_required_roles_in_pipe()
    {
        $guard = '';
        $user = $this->getMember();

        $user->assignRole('writer');

        auth()->setUser($user);

        $this->assertEquals('does not have all of the given roles', $this->renderView('guardHasAllRolesPipe', compact('guard')));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_roles_in_array()
    {
        $guard = 'admin';

        $admin = $this->getSuperAdmin();

        $admin->assignRole('moderator');

        auth('admin')->setUser($admin);

        $this->assertEquals('does have all of the given roles', $this->renderView('guardHasAllRolesArray', compact('guard')));
    }

    /** @test */
    #[Test]
    public function the_hasallroles_directive_will_evaluate_false_when_the_logged_in_user_doesnt_have_all_required_roles_in_array()
    {
        $guard = '';
        $user = $this->getMember();

        $user->assignRole('writer');

        auth()->setUser($user);

        $this->assertEquals('does not have all of the given roles', $this->renderView('guardHasAllRolesArray', compact('guard')));
    }

    protected function getWriter()
    {
        $this->testUser->assignRole('writer');

        return $this->testUser;
    }

    protected function getMember()
    {
        $this->testUser->assignRole('member');

        return $this->testUser;
    }

    protected function getSuperAdmin()
    {
        $this->testAdmin->assignRole('super-admin');

        return $this->testAdmin;
    }

    protected function renderView($view, $parameters)
    {
        Artisan::call('view:clear');

        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }
}
