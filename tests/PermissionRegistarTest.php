<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestModels\Permission as TestPermission;
use Spatie\Permission\Tests\TestModels\Role as TestRole;

class PermissionRegistarTest extends TestCase
{
    /** @test */
    public function it_can_clear_loaded_permissions_collection()
    {
        $reflectedClass = new \ReflectionClass(app(PermissionRegistrar::class));
        $reflectedProperty = $reflectedClass->getProperty('permissions');
        $reflectedProperty->setAccessible(true);

        app(PermissionRegistrar::class)->getPermissions();

        $this->assertNotNull($reflectedProperty->getValue(app(PermissionRegistrar::class)));

        app(PermissionRegistrar::class)->clearPermissionsCollection();

        $this->assertNull($reflectedProperty->getValue(app(PermissionRegistrar::class)));
    }

    /** @test */
    public function it_can_check_uids()
    {
        $uids = [
            // UUIDs
            '00000000-0000-0000-0000-000000000000',
            '9be37b52-e1fa-4e86-b65f-cbfcbedde838',
            'fc458041-fb21-4eea-a04b-b55c87a7224a',
            '78144b52-a889-11ed-afa1-0242ac120002',
            '78144f4e-a889-11ed-afa1-0242ac120002',
            // GUIDs
            '4b8590bb-90a2-4f38-8dc9-70e663a5b0e5',
            'A98C5A1E-A742-4808-96FA-6F409E799937',
            '1f01164a-98e9-4246-93ec-7941aefb1da6',
            '91b73d20-89e6-46b0-b39b-632706cc3ed7',
            '0df4a5b8-7c2e-484f-ad1d-787d1b83aacc',
            // ULIDs
            '01GRVB3DREB63KNN4G2QVV99DF',
            '01GRVB3DRECY317SJCJ6DMTFCA',
            '01GRVB3DREGGPBXNH1M24GX1DS',
            '01GRVB3DRESRM2K9AVQSW1JCKA',
            '01GRVB3DRES5CQ31PB24MP4CSV',
        ];

        $not_uids = [
            '9be37b52-e1fa',
            '9be37b52-e1fa-4e86',
            '9be37b52-e1fa-4e86-b65f',
            '01GRVB3DREB63KNN4G2',
            'TEST STRING',
            '00-00-00-00-00-00',
            '91GRVB3DRES5CQ31PB24MP4CSV',
        ];

        foreach ($uids as $uid) {
            $this->assertTrue(PermissionRegistrar::isUid($uid));
        }

        foreach ($not_uids as $not_uid) {
            $this->assertFalse(PermissionRegistrar::isUid($not_uid));
        }
    }

    /** @test */
    public function it_can_get_permission_class()
    {
        $this->assertSame(SpatiePermission::class, app(PermissionRegistrar::class)->getPermissionClass());
        $this->assertSame(SpatiePermission::class, get_class(app(PermissionContract::class)));
    }

    /** @test */
    public function it_can_change_permission_class()
    {
        $this->assertSame(SpatiePermission::class, config('permission.models.permission'));
        $this->assertSame(SpatiePermission::class, app(PermissionRegistrar::class)->getPermissionClass());
        $this->assertSame(SpatiePermission::class, get_class(app(PermissionContract::class)));

        app(PermissionRegistrar::class)->setPermissionClass(TestPermission::class);

        $this->assertSame(TestPermission::class, config('permission.models.permission'));
        $this->assertSame(TestPermission::class, app(PermissionRegistrar::class)->getPermissionClass());
        $this->assertSame(TestPermission::class, get_class(app(PermissionContract::class)));
    }

    /** @test */
    public function it_can_get_role_class()
    {
        $this->assertSame(SpatieRole::class, app(PermissionRegistrar::class)->getRoleClass());
        $this->assertSame(SpatieRole::class, get_class(app(RoleContract::class)));
    }

    /** @test */
    public function it_can_change_role_class()
    {
        $this->assertSame(SpatieRole::class, config('permission.models.role'));
        $this->assertSame(SpatieRole::class, app(PermissionRegistrar::class)->getRoleClass());
        $this->assertSame(SpatieRole::class, get_class(app(RoleContract::class)));

        app(PermissionRegistrar::class)->setRoleClass(TestRole::class);

        $this->assertSame(TestRole::class, config('permission.models.role'));
        $this->assertSame(TestRole::class, app(PermissionRegistrar::class)->getRoleClass());
        $this->assertSame(TestRole::class, get_class(app(RoleContract::class)));
    }

    /** @test */
    public function it_can_change_team_id()
    {
        $team_id = '00000000-0000-0000-0000-000000000000';

        app(PermissionRegistrar::class)->setPermissionsTeamId($team_id);

        $this->assertSame($team_id, app(PermissionRegistrar::class)->getPermissionsTeamId());
    }
}
