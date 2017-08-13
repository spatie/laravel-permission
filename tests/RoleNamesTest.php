<?php

namespace Spatie\Permission\Test;

class RoleNamesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $roleModel = app(Role::class);

        $roleModel->create(['name' => 'member']);
        $roleModel->create(['name' => 'writer']);
        $roleModel->create(['name' => 'intern']);
        $roleModel->create(['name' => 'super-admin']);
    }

    /** @test */
    public function get_role_names_can_show_a_single_role_name()
    {
        $this->testUser->assignRole('member');
        $this->assertSame(getRoleNames($this->testUser), 'Member');

        $this->refreshTestUser();

        $this->testUser->assignRole('super-admin');
        $this->assertSame(getRoleNames($this->testUser), 'Super-admin');
    }

    /** @test */
    public function get_role_names_can_show_multiple_role_names()
    {
        $this->testUser->assignRole('member');
        $this->testUser->assignRole('writer');
        $this->testUser->assignRole('intern');
        $this->testUser->assignRole('super-admin');

        $this->assertContains(getRoleNames($this->testUser), ['Member', 'Writer', 'Intern', 'Super-admin']);
    }

    /** @test */
    public function get_role_names_raw_can_show_a_single_role_name()
    {
        $this->testUser->assignRole('member');
        $this->assertSame(getRoleNames($this->testUser), '["member"]');

        $this->refreshTestUser();

        $this->testUser->assignRole('super-admin');
        $this->assertSame(getRoleNames($this->testUser), '["super-admin"]');
    }

    /** @test */
    public function get_role_names_raw_can_show_multiple_role_names()
    {
        $this->testUser->assignRole('member');
        $this->testUser->assignRole('writer');
        $this->testUser->assignRole('intern');
        $this->testUser->assignRole('super-admin');

        $this->assertContains(getRoleNames($this->testUser), ['["member"]', '["writer"]', '["intern"]', '["super-admin"]']);
    }
}
