<?php

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
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

it('clears the loaded permissions collection when reinitializing the cache', function () {
    $reflectedClass = new ReflectionClass(app(PermissionRegistrar::class));
    $reflectedProperty = $reflectedClass->getProperty('permissions');
    $reflectedProperty->setAccessible(true);

    app(PermissionRegistrar::class)->getPermissions();

    expect($reflectedProperty->getValue(app(PermissionRegistrar::class)))->not->toBeNull();

    app(PermissionRegistrar::class)->initializeCache();

    expect($reflectedProperty->getValue(app(PermissionRegistrar::class)))->toBeNull();
});

it('does not leak a previous tenant\'s permissions after switching cache context via initializeCache', function () {
    // Two separate cache "stores" stand in for two tenants' cache namespaces
    // (e.g. distinct cache prefixes/connections in a real multi-tenant app).
    config([
        'cache.stores.tenant_a' => ['driver' => 'array'],
        'cache.stores.tenant_b' => ['driver' => 'array'],
    ]);

    // Insert both tenants' rows via the query builder, bypassing Eloquent,
    // so the RefreshesPermissionCache model events don't auto-bust the cache and
    // mask the very staleness this test is meant to catch.
    $tenantAId = DB::table('permissions')->insertGetId([
        'name' => 'tenant-permission',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    config(['permission.cache.store' => 'tenant_a']);
    app(PermissionRegistrar::class)->initializeCache();

    $loaded = app(PermissionRegistrar::class)->getPermissions()->firstWhere('name', 'tenant-permission');
    expect($loaded->getKey())->toBe($tenantAId);

    // Simulate switching to tenant B: its own row for the "same" permission has
    // a different primary key, as it would in a separate tenant database.
    DB::table('permissions')->where('id', $tenantAId)->delete();
    $tenantBId = DB::table('permissions')->insertGetId([
        'name' => 'tenant-permission',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    expect($tenantBId)->not->toBe($tenantAId);

    config(['permission.cache.store' => 'tenant_b']);
    app(PermissionRegistrar::class)->initializeCache();

    $loaded = app(PermissionRegistrar::class)->getPermissions()->firstWhere('name', 'tenant-permission');
    expect($loaded->getKey())->toBe($tenantBId);
});

it('picks up a rebound cache manager when initializeCache runs after the container cache binding is replaced', function () {
    // Neither the array nor the file store apply cache.prefix (only
    // apc/database/redis/memcached/dynamodb do), so this needs a store that
    // actually honours the prefix to observe the staleness.
    if (! Schema::hasTable('cache')) {
        $this->createCacheTable();
    }
    config()->set('cache.default', 'database');

    // This mirrors what spatie/laravel-multitenancy's PrefixCacheTask does on every
    // tenant switch: change cache.prefix, then forget the container's cache
    // singletons so they get rebuilt against the new prefix.
    $switchPrefix = function (string $prefix) {
        config()->set('cache.prefix', $prefix);
        app('cache')->forgetDriver(config('cache.default'));
        app()->forgetInstance('cache');
        app()->forgetInstance('cache.store');
        Cache::clearResolvedInstances();
    };

    $switchPrefix('tenant_a_');
    app(PermissionRegistrar::class)->initializeCache();
    expect(app(PermissionRegistrar::class)->getCacheStore()->getPrefix())->toBe('tenant_a_');

    $switchPrefix('tenant_b_');
    app(PermissionRegistrar::class)->initializeCache();
    expect(app(PermissionRegistrar::class)->getCacheStore()->getPrefix())->toBe('tenant_b_');
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

it('can change team id using a model instance', function () {
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->testUser);

    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBe($this->testUser->getKey());
});

it('falls back to the array cache store when an undefined cache store is configured', function () {
    config()->set('permission.cache.store', 'this-store-does-not-exist');

    app(PermissionRegistrar::class)->initializeCache();

    expect(app(PermissionRegistrar::class)->getCacheStore())
        ->toBeInstanceOf(ArrayStore::class);
});

it('retries loading permissions when another load is already in progress', function () {
    $registrar = app(PermissionRegistrar::class);
    $registrar->forgetCachedPermissions();

    $reflectedClass = new ReflectionClass($registrar);
    $loadingProperty = $reflectedClass->getProperty('isLoadingPermissions');
    $loadingProperty->setAccessible(true);
    $loadingProperty->setValue($registrar, true);

    $permissions = $registrar->getPermissions();

    expect($permissions)->toBeInstanceOf(Collection::class);
    expect($loadingProperty->getValue($registrar))->toBeFalse();
});

it('resolves single-record lookups by name and by key from the index', function () {
    $registrar = app(PermissionRegistrar::class);

    foreach ($registrar->getPermissions() as $permission) {
        $byName = $registrar->getPermissions(['name' => $permission->name, 'guard_name' => $permission->guard_name], true);
        $byKey = $registrar->getPermissions([$permission->getKeyName() => $permission->getKey(), 'guard_name' => $permission->guard_name], true);

        expect($byName)->toHaveCount(1)
            ->and($byName->first())->toBe($permission)
            ->and($byKey)->toHaveCount(1)
            ->and($byKey->first())->toBe($permission);
    }
});

it('returns the same permission instance for indexed and filtered lookups', function () {
    $registrar = app(PermissionRegistrar::class);
    $params = ['name' => 'edit-articles', 'guard_name' => 'web'];

    expect($registrar->getPermissions($params, true)->first())
        ->toBe($registrar->getPermissions($params)->first());
});

it('does not resolve an indexed lookup across guards', function () {
    $registrar = app(PermissionRegistrar::class);

    expect($registrar->getPermissions(['name' => 'edit-articles', 'guard_name' => 'admin'], true))->toBeEmpty()
        ->and($registrar->getPermissions(['name' => 'admin-permission', 'guard_name' => 'web'], true))->toBeEmpty()
        ->and($registrar->getPermissions(['name' => 'admin-permission', 'guard_name' => 'admin'], true))->toHaveCount(1);
});

it('still filters lookups that are not indexable', function () {
    $registrar = app(PermissionRegistrar::class);

    expect($registrar->getPermissions(['guard_name' => 'web'])->count())
        ->toBe($registrar->getPermissions()->where('guard_name', 'web')->count())
        ->and($registrar->getPermissions(['guard_name' => 'web'], true))->toHaveCount(1)
        ->and($registrar->getPermissions(['name' => 'edit-articles'], true))->toHaveCount(1)
        ->and($registrar->getPermissions(['name' => 'edit-articles', 'guard_name' => 'web', 'id' => 0], true))->toBeEmpty();
});

it('rebuilds the lookup index when the permission cache is flushed', function () {
    $registrar = app(PermissionRegistrar::class);

    expect(fn () => SpatiePermission::findByName('late-permission'))->toThrow(PermissionDoesNotExist::class);

    $created = SpatiePermission::create(['name' => 'late-permission']);

    expect(SpatiePermission::findByName('late-permission')->getKey())->toBe($created->getKey())
        ->and(SpatiePermission::findById($created->getKey())->name)->toBe('late-permission');

    $registrar->clearPermissionsCollection();

    expect(SpatiePermission::findByName('late-permission')->getKey())->toBe($created->getKey());
});

it('preserves loose comparisons and the first matching permission', function (string $attribute, mixed $value) {
    DB::table('permissions')->insert([
        ['name' => '01', 'guard_name' => 'web'],
        ['name' => '1', 'guard_name' => 'web'],
        ['name' => 'numeric-guard', 'guard_name' => '01'],
        ['name' => 'numeric-guard', 'guard_name' => '1'],
    ]);

    $registrar = app(PermissionRegistrar::class);
    $registrar->forgetCachedPermissions();
    $params = match ($attribute) {
        'name' => ['name' => $value, 'guard_name' => 'web'],
        'guard_name' => ['name' => 'numeric-guard', 'guard_name' => $value],
        'id' => ['id' => $value, 'guard_name' => 'web'],
    };
    $expectedName = match ($attribute) {
        'name' => '01',
        'guard_name' => 'numeric-guard',
        'id' => 'edit-articles',
    };

    $permission = $registrar->getPermissions($params, true)->first();

    expect($permission?->name)->toBe($expectedName);

    if ($attribute === 'guard_name') {
        expect($permission->guard_name)->toBe('01');
    }
})->with([
    ['name', '1'], ['name', '1e0'], ['name', 1],
    ['guard_name', '1'], ['guard_name', 1],
    ['id', '01'], ['id', '1e0'], ['id', '1.0'], ['id', '+1'], ['id', ' 1 '],
    ['id', true], ['id', 1.0],
]);

it('observes mutations of returned permission models without requiring a save', function (string $attribute, mixed $value, bool $multiple) {
    $registrar = app(PermissionRegistrar::class);
    $permission = $multiple
        ? $registrar->getPermissions(['name' => 'edit-articles'])->first()
        : SpatiePermission::findByName('edit-articles');
    $oldParams = ['name' => 'edit-articles', 'guard_name' => 'web'];
    $params = [$attribute => $value, 'guard_name' => 'web'];

    $permission->setAttribute($attribute, $value);
    $permission->syncOriginal();

    if ($attribute === 'guard_name') {
        $params = ['name' => 'edit-articles', 'guard_name' => $value];
    }

    expect($registrar->getPermissions($params, true)->first())->toBe($permission);

    if ($attribute !== 'id') {
        expect($registrar->getPermissions($oldParams, true))->toBeEmpty();
    }
})->with([
    ['name', 'renamed', false],
    ['name', 'edit-news', false],
    ['guard_name', 'admin', false],
    ['id', 999, false],
    ['name', 'renamed', true],
]);

it('observes runtime casts on returned permission models', function () {
    $permission = SpatiePermission::findByName('edit-articles');
    $permission->mergeCasts(['name' => 'boolean']);

    expect(app(PermissionRegistrar::class)->getPermissions(['name' => 'anything', 'guard_name' => 'web'], true)->first())
        ->toBe($permission);
});

it('uses current custom accessors without eagerly evaluating unrelated attributes', function () {
    $registrar = app(PermissionRegistrar::class);
    $registrar->setPermissionClass(LookupPermission::class)->forgetCachedPermissions();
    config(['testing.permission_prefix' => 'first-']);

    $permission = LookupPermission::findByName('first-edit-articles');

    config(['testing.permission_prefix' => 'second-']);

    expect(LookupPermission::findByName('second-edit-articles'))->toBe($permission)
        ->and($registrar->getPermissions(['name' => 'first-edit-articles', 'guard_name' => 'web'], true))->toBeEmpty();

    $registrar->clearPermissionsCollection();
    config(['testing.permission_prefix' => null]);

    expect($registrar->getPermissions(['guard_name' => 'web'], true))->toHaveCount(1);
});

it('supports custom permission keys across cache hydration', function () {
    $this->setUpCustomModels();
    $registrar = app(PermissionRegistrar::class);

    $permission = TestPermission::findByName('edit-articles');

    expect(TestPermission::findById($permission->getKey()))->toBe($permission)
        ->and($registrar->getPermissions(['permission_test_id' => $permission->getKey(), 'guard_name' => 'admin'], true))->toBeEmpty();

    $registrar->clearPermissionsCollection();

    expect(TestPermission::findById($permission->getKey())->name)->toBe('edit-articles');
});

it('returns the first matching model from custom collections', function (bool $keyed) {
    config(['testing.keyed_permissions' => $keyed]);
    $registrar = new class(app(CacheManager::class)) extends PermissionRegistrar
    {
        protected function getPermissionsWithRoles(): Collection
        {
            $permissions = parent::getPermissionsWithRoles();
            $permissions[1]->name = $permissions[0]->name;

            return new LookupPermissionCollection(config('testing.keyed_permissions')
                ? $permissions->keyBy('id')->all()
                : $permissions->all());
        }
    };
    $registrar->forgetCachedPermissions();

    $permissions = $registrar->getPermissions(['name' => 'edit-articles', 'guard_name' => 'web'], true);

    expect($permissions)->toBeInstanceOf(Collection::class)->toHaveCount(1)
        ->and($permissions->first()->getKey())->toBe($this->testUserPermission->getKey())
        ->and($registrar->getPermissions(['name' => 'edit-articles'])->count())->toBe(2);
})->with([true, false]);

it('preserves lookup comparisons when the primary key is excluded from the cache', function () {
    config(['permission.cache.column_names_except' => ['id']]);
    $registrar = app(PermissionRegistrar::class);
    $registrar->forgetCachedPermissions();

    SpatiePermission::preventAccessingMissingAttributes();

    try {
        expect($registrar->getPermissions(['guard_name' => 'web'], true)->first()?->name)
            ->toBe('edit-articles');
    } finally {
        SpatiePermission::preventAccessingMissingAttributes(false);
    }

    expect($registrar->getPermissions(['id' => 0, 'guard_name' => 'web'], true)->first()?->name)
        ->toBe('edit-articles');
});

it('invalidates lookups after saved name and guard changes and deletion', function () {
    $permission = SpatiePermission::findByName('edit-articles');
    $id = $permission->getKey();
    $permission->update(['name' => 'renamed', 'guard_name' => 'admin']);

    expect(fn () => SpatiePermission::findByName('edit-articles'))->toThrow(PermissionDoesNotExist::class)
        ->and(fn () => SpatiePermission::findById($id, 'web'))->toThrow(PermissionDoesNotExist::class)
        ->and(SpatiePermission::findByName('renamed', 'admin')->getKey())->toBe($id);

    SpatiePermission::findById($id, 'admin')->delete();

    expect(fn () => SpatiePermission::findByName('renamed', 'admin'))->toThrow(PermissionDoesNotExist::class)
        ->and(fn () => SpatiePermission::findById($id, 'admin'))->toThrow(PermissionDoesNotExist::class);
});

it('releases indexed models when clearing the in memory cache', function (string $method) {
    $permission = SpatiePermission::findByName('edit-articles');
    $reference = WeakReference::create($permission);
    unset($permission);

    app(PermissionRegistrar::class)->$method();
    gc_collect_cycles();

    expect($reference->get())->toBeNull()
        ->and(SpatiePermission::findByName('edit-articles')->name)->toBe('edit-articles');
})->with(['clearPermissionsCollection', 'forgetCachedPermissions', 'initializeCache']);

class LookupPermission extends SpatiePermission
{
    public function getNameAttribute(string $value): string
    {
        $prefix = config('testing.permission_prefix');

        if ($prefix === null) {
            throw new LogicException('The name accessor should not be evaluated.');
        }

        return $prefix.$value;
    }

    public function newCollection(array $models = []): LookupPermissionCollection
    {
        return new LookupPermissionCollection($models);
    }
}

class LookupPermissionCollection extends Collection {}
