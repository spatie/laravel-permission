<?php

namespace Spatie\Permission\Test;

class BladeTest extends TestCase
{
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
    public function all_blade_directives_will_evaluate_falsly_when_somebody_without_roles_or_permissions_is_logged_in()
    {
        $role = 'admin';
        $roles = [$role];

        $this->actingAs($this->userWithoutRolesOrPermissions);

        $this->assertEquals('does not have role', $this->renderView('role', [$role]));
        //$this->assertEquals('does not have role', $this->renderView('hasrole', [$role]));
        //$this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', $roles));
        //$this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', $roles));
    }

    public function renderView($view, $parameters)
    {
        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }

        return trim((string) ($view));
    }
}
