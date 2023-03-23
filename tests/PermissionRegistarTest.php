<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\PermissionRegistrar;

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
}
