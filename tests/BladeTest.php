<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Role;

class BladeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Role::create(['name' => 'member']);
        Role::create(['name' => 'admin']);
    }

    /**
     * @test
     */
    public function all_blade_directives_will_evaluate_falsly_when_there_is_nobody_logged_in()
    {
        $role = 'admin';
        $roles = [$role];

        $this->assertEquals('does not have role', $this->renderView('role', [$role]));
        $this->assertEquals('does not have role', $this->renderView('hasrole', [$role]));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', $roles));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', $roles));
    }

    /**
     * @test
     */
    public function all_blade_directives_will_evaluate_falsy_when_somebody_without_roles_or_permissions_is_logged_in()
    {
        $role = 'admin';
        $roles = 'admin';

        $this->actingAs($this->testUser);

        $this->assertEquals('does not have role', $this->renderView('role', compact('role')));
        $this->assertEquals('does not have role', $this->renderView('hasrole', compact('role')));
        $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
        $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
    }

    /**
     * @test
     */
    public function the_role_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->login($this->getAdmin());

        $this->assertEquals('has role', $this->renderView('role', ['role' => 'admin']));
    }

    /**
     * @test
     */
    public function the_hasrole_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
    {
        auth()->login($this->getAdmin());

        $this->assertEquals('has role', $this->renderView('hasrole', ['role' => 'admin']));
    }

    /**
     * @test
     */
    public function the_hasallroles_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_all_required_roles()
    {
        $roles = ['member', 'admin'];

        auth()->login($this->getMember());

        $this->assertEquals('does not have all of the given roles', $this->renderView('hasallroles', compact('roles')));
    }

    /**
     * @test
     */
    public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_does_have_all_required_roles()
    {
        $roles = ['member', 'admin'];

        $user = $this->getMember();

        $user->assignRole('admin');

        $this->refreshTestUser();

        auth()->login($user);

        $this->assertEquals('does have all of the given roles', $this->renderView('hasallroles', compact('roles')));
    }

    public function getAdmin()
    {
        $this->testUser->assignRole('admin');

        $this->refreshTestUser();

        return $this->testUser;
    }

    public function getMember()
    {
        $this->testUser->assignRole('member');

        $this->refreshTestUser();

        return $this->testUser;
    }

    public function renderView($view, $parameters)
    {
        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }
}
