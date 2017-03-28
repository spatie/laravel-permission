<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;

class BladeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $roleModel = app(Role::class);

        $roleModel->create(['name' => 'member']);
        $roleModel->create(['name' => 'writer']);
        $roleModel->create(['name' => 'super-admin', 'guard_name' => 'admin']);
    }

    /** @test */
    public function all_blade_directives_will_evaluate_falsly_when_there_is_nobody_logged_in()
    {
        $role = 'writer';
        $roles = [$role];

        $this->assertEquals('does not have role', $this->renderView('role', [$role]));
        $this->assertEquals('does not have role', $this->renderView('hasRole', [$role]));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', $roles));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', $roles));
    }

    /** @test */
    public function all_blade_directives_will_evaluate_falsy_when_somebody_without_roles_or_permissions_is_logged_in()
    {
        $role = 'writer';
        $roles = 'writer';

        $this->actingAs($this->testUser);

        $this->assertEquals('does not have role', $this->renderView('role', compact('role')));
        $this->assertEquals('does not have role', $this->renderView('hasRole', compact('role')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
    }

    /** @test */
    public function all_blade_directives_will_evaluate_falsy_when_somebody_with_another_guard_is_logged_in()
    {
        $role = 'writer';
        $roles = 'writer';

        $this->actingAs($this->testAdmin);

        $this->assertEquals('does not have role', $this->renderView('role', compact('role')));
        $this->assertEquals('does not have role', $this->renderView('hasRole', compact('role')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
    }

    /** @test */
    public function the_role_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->login($this->getWriter());

        $this->assertEquals('has role', $this->renderView('role', ['role' => 'writer']));

        auth()->login($this->getSuperAdmin());

        $this->assertEquals('has role', $this->renderView('role', ['role' => 'super-admin']));
    }

    /** @test */
    public function the_hasrole_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->login($this->getWriter());

        $this->assertEquals('has role', $this->renderView('hasRole', ['role' => 'writer']));

        auth()->login($this->getSuperAdmin());

        $this->assertEquals('has role', $this->renderView('hasRole', ['role' => 'super-admin']));
    }

    /** @test */
    public function the_hasallroles_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_all_required_roles()
    {
        $roles = ['member', 'writer'];

        auth()->login($this->getMember());

        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
    }

    /** @test */
    public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_roles()
    {
        $roles = ['member', 'writer'];

        $user = $this->getMember();

        $user->assignRole('writer');

        $this->refreshTestUser();

        auth()->login($user);

        $this->assertEquals('does have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
    }

    public function getWriter()
    {
        $this->testUser->assignRole('writer');

        $this->refreshTestUser();

        return $this->testUser;
    }

    public function getMember()
    {
        $this->testUser->assignRole('member');

        $this->refreshTestUser();

        return $this->testUser;
    }

    public function getSuperAdmin()
    {
        $this->testAdmin->assignRole('super-admin');

        $this->refreshTestAdmin();

        return $this->testAdmin;
    }

    public function renderView($view, $parameters)
    {
        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }
}
