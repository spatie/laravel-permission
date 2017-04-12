<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;

class PermissionTest extends TestCase
{
    /** @test */
    public function it_throws_an_exception_when_the_permission_already_exists()
    {
        $this->expectException(PermissionAlreadyExists::class);

        app(Permission::class)->create(['name' => 'test-permission']);
        app(Permission::class)->create(['name' => 'test-permission']);
    }
}
