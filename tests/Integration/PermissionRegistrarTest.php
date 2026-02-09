<?php

use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\Permission as TestPermission;
use Spatie\Permission\Tests\TestSupport\TestModels\Role as TestRole;

it('can clear loaded permissions collection', function () {
    $reflectedClass = new ReflectionClass(app(PermissionRegistrar::class));
    $reflectedProperty = $reflectedClass->getProperty('permissions');
    $reflectedProperty->setAccessible(true);

    app(PermissionRegistrar::class)->getPermissions();

    expect($reflectedProperty->getValue(app(PermissionRegistrar::class)))->not->toBeNull();

    app(PermissionRegistrar::class)->clearPermissionsCollection();

    expect($reflectedProperty->getValue(app(PermissionRegistrar::class)))->toBeNull();
});

it('can check uids', function () {
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
        expect(PermissionRegistrar::isUid($uid))->toBeTrue();
    }

    foreach ($not_uids as $not_uid) {
        expect(PermissionRegistrar::isUid($not_uid))->toBeFalse();
    }
});

it('can get permission class', function () {
    expect(app(PermissionRegistrar::class)->getPermissionClass())->toBe(SpatiePermission::class);
    expect(get_class(app(PermissionContract::class)))->toBe(SpatiePermission::class);
});

it('can change permission class', function () {
    expect(config('permission.models.permission'))->toBe(SpatiePermission::class);
    expect(app(PermissionRegistrar::class)->getPermissionClass())->toBe(SpatiePermission::class);
    expect(get_class(app(PermissionContract::class)))->toBe(SpatiePermission::class);

    app(PermissionRegistrar::class)->setPermissionClass(TestPermission::class);

    expect(config('permission.models.permission'))->toBe(TestPermission::class);
    expect(app(PermissionRegistrar::class)->getPermissionClass())->toBe(TestPermission::class);
    expect(get_class(app(PermissionContract::class)))->toBe(TestPermission::class);
});

it('can get role class', function () {
    expect(app(PermissionRegistrar::class)->getRoleClass())->toBe(SpatieRole::class);
    expect(get_class(app(RoleContract::class)))->toBe(SpatieRole::class);
});

it('can change role class', function () {
    expect(config('permission.models.role'))->toBe(SpatieRole::class);
    expect(app(PermissionRegistrar::class)->getRoleClass())->toBe(SpatieRole::class);
    expect(get_class(app(RoleContract::class)))->toBe(SpatieRole::class);

    app(PermissionRegistrar::class)->setRoleClass(TestRole::class);

    expect(config('permission.models.role'))->toBe(TestRole::class);
    expect(app(PermissionRegistrar::class)->getRoleClass())->toBe(TestRole::class);
    expect(get_class(app(RoleContract::class)))->toBe(TestRole::class);
});

it('can change team id', function () {
    $team_id = '00000000-0000-0000-0000-000000000000';

    app(PermissionRegistrar::class)->setPermissionsTeamId($team_id);

    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBe($team_id);
});
