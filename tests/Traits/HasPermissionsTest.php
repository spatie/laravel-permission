<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Tests\TestSupport\TestModels\SoftDeletingUser;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

it('can assign a permission to a user', function () {
    $this->testUser->givePermissionTo($this->testUserPermission);

    expect($this->testUser->hasPermissionTo($this->testUserPermission))->toBeTrue();
});

it('can assign a permission to a user with a non default guard', function () {
    $testUserPermission = app(Permission::class)->create([
        'name' => 'edit-articles',
        'guard_name' => 'api',
    ]);

    $this->testUser->givePermissionTo($testUserPermission);

    expect($this->testUser->hasPermissionTo($testUserPermission))->toBeTrue();
});

it('throws an exception when assigning a permission that does not exist', function () {
    expect(fn () => $this->testUser->givePermissionTo('permission-does-not-exist'))
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when assigning a permission to a user from a different guard', function () {
    expect(fn () => $this->testUser->givePermissionTo($this->testAdminPermission))
        ->toThrow(GuardDoesNotMatch::class);

    expect(fn () => $this->testUser->givePermissionTo('admin-permission'))
        ->toThrow(PermissionDoesNotExist::class);
});

it('can revoke a permission from a user', function () {
    $this->testUser->givePermissionTo($this->testUserPermission);

    expect($this->testUser->hasPermissionTo($this->testUserPermission))->toBeTrue();

    $this->testUser->revokePermissionTo($this->testUserPermission);

    expect($this->testUser->hasPermissionTo($this->testUserPermission))->toBeFalse();
});

it('can assign and remove a permission using enums', function () {
    $enum = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::ViewArticles;

    $permission = app(Permission::class)->findOrCreate($enum->value, 'web');

    $this->testUser->givePermissionTo($enum);

    expect($this->testUser->hasPermissionTo($enum))->toBeTrue();
    expect($this->testUser->hasAnyPermission($enum))->toBeTrue();
    expect($this->testUser->hasDirectPermission($enum))->toBeTrue();

    $this->testUser->revokePermissionTo($enum);

    expect($this->testUser->hasPermissionTo($enum))->toBeFalse();
    expect($this->testUser->hasAnyPermission($enum))->toBeFalse();
    expect($this->testUser->hasDirectPermission($enum))->toBeFalse();
});

it('can scope users using enums', function () {
    $enum1 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::ViewArticles;
    $enum2 = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::EditArticles;
    $permission1 = app(Permission::class)->findOrCreate($enum1->value, 'web');
    $permission2 = app(Permission::class)->findOrCreate($enum2->value, 'web');

    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->givePermissionTo([$enum1, $enum2]);
    $this->testUserRole->givePermissionTo($enum2);
    $user2->assignRole('testRole');

    $scopedUsers1 = User::permission($enum2)->get();
    $scopedUsers2 = User::permission([$enum1])->get();
    $scopedUsers3 = User::withoutPermission([$enum1])->get();
    $scopedUsers4 = User::withoutPermission([$enum2])->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(2);
    expect($scopedUsers4->count())->toEqual(1);
});

it('can scope users using a string', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->givePermissionTo(['edit-articles', 'edit-news']);
    $this->testUserRole->givePermissionTo('edit-articles');
    $user2->assignRole('testRole');

    $scopedUsers1 = User::permission('edit-articles')->get();
    $scopedUsers2 = User::permission(['edit-news'])->get();
    $scopedUsers3 = User::withoutPermission('edit-news')->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(2);
});

it('can scope users using a int', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->givePermissionTo([1, 2]);
    $this->testUserRole->givePermissionTo(1);
    $user2->assignRole('testRole');

    $scopedUsers1 = User::permission(1)->get();
    $scopedUsers2 = User::permission([2])->get();
    $scopedUsers3 = User::withoutPermission([2])->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(2);
});

it('can scope users using an array', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->givePermissionTo(['edit-articles', 'edit-news']);
    $this->testUserRole->givePermissionTo('edit-articles');
    $user2->assignRole('testRole');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::permission(['edit-articles', 'edit-news'])->get();
    $scopedUsers2 = User::permission(['edit-news'])->get();
    $scopedUsers3 = User::withoutPermission(['edit-news'])->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(2);
});

it('can scope users using a collection', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->givePermissionTo(['edit-articles', 'edit-news']);
    $this->testUserRole->givePermissionTo('edit-articles');
    $user2->assignRole('testRole');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::permission(collect(['edit-articles', 'edit-news']))->get();
    $scopedUsers2 = User::permission(collect(['edit-news']))->get();
    $scopedUsers3 = User::withoutPermission(collect(['edit-news']))->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(2);
});

it('can scope users using an object', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user1->givePermissionTo($this->testUserPermission->name);

    $scopedUsers1 = User::permission($this->testUserPermission)->get();
    $scopedUsers2 = User::permission([$this->testUserPermission])->get();
    $scopedUsers3 = User::permission(collect([$this->testUserPermission]))->get();
    $scopedUsers4 = User::withoutPermission(collect([$this->testUserPermission]))->get();

    expect($scopedUsers1->count())->toEqual(1);
    expect($scopedUsers2->count())->toEqual(1);
    expect($scopedUsers3->count())->toEqual(1);
    expect($scopedUsers4->count())->toEqual(0);
});

it('can scope users without direct permissions only role', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $this->testUserRole->givePermissionTo('edit-articles');
    $user1->assignRole('testRole');
    $user2->assignRole('testRole');
    $user3->assignRole('testRole2');

    $scopedUsers1 = User::permission('edit-articles')->get();
    $scopedUsers2 = User::withoutPermission('edit-articles')->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
});

it('can scope users with only direct permission', function () {
    User::all()->each(fn ($item) => $item->delete());
    $user1 = User::create(['email' => 'user1@test.com']);
    $user2 = User::create(['email' => 'user2@test.com']);
    $user3 = User::create(['email' => 'user3@test.com']);
    $user1->givePermissionTo(['edit-news']);
    $user2->givePermissionTo(['edit-articles', 'edit-news']);

    $scopedUsers1 = User::permission('edit-news')->get();
    $scopedUsers2 = User::withoutPermission('edit-news')->get();

    expect($scopedUsers1->count())->toEqual(2);
    expect($scopedUsers2->count())->toEqual(1);
});

it('throws an exception when calling hasPermissionTo with an invalid type', function () {
    $user = User::create(['email' => 'user1@test.com']);

    expect(fn () => $user->hasPermissionTo(new \stdClass))
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when calling hasPermissionTo with null', function () {
    $user = User::create(['email' => 'user1@test.com']);

    expect(fn () => $user->hasPermissionTo(null))
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when calling hasDirectPermission with an invalid type', function () {
    $user = User::create(['email' => 'user1@test.com']);

    expect(fn () => $user->hasDirectPermission(new \stdClass))
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when calling hasDirectPermission with null', function () {
    $user = User::create(['email' => 'user1@test.com']);

    expect(fn () => $user->hasDirectPermission(null))
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when trying to scope a non existing permission', function () {
    expect(fn () => User::permission('not defined permission')->get())
        ->toThrow(PermissionDoesNotExist::class);

    expect(fn () => User::withoutPermission('not defined permission')->get())
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when trying to scope a permission from another guard', function () {
    expect(fn () => User::permission('testAdminPermission')->get())
        ->toThrow(PermissionDoesNotExist::class);

    expect(fn () => User::withoutPermission('testAdminPermission')->get())
        ->toThrow(PermissionDoesNotExist::class);
});

it('doesnt detach permissions when user soft deleting', function () {
    $user = SoftDeletingUser::create(['email' => 'test@example.com']);
    $user->givePermissionTo(['edit-news']);
    $user->delete();

    $user = SoftDeletingUser::withTrashed()->find($user->id);

    expect($user->hasPermissionTo('edit-news'))->toBeTrue();
});

it('can give and revoke multiple permissions', function () {
    $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

    expect($this->testUserRole->permissions()->count())->toEqual(2);

    $this->testUserRole->revokePermissionTo(['edit-articles', 'edit-news']);

    expect($this->testUserRole->permissions()->count())->toEqual(0);
});

it('can give and revoke permissions models array', function () {
    $models = [app(Permission::class)::where('name', 'edit-articles')->first(), app(Permission::class)::where('name', 'edit-news')->first()];

    $this->testUserRole->givePermissionTo($models);

    expect($this->testUserRole->permissions()->count())->toEqual(2);

    $this->testUserRole->revokePermissionTo($models);

    expect($this->testUserRole->permissions()->count())->toEqual(0);
});

it('can give and revoke permissions models collection', function () {
    $models = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])->get();

    $this->testUserRole->givePermissionTo($models);

    expect($this->testUserRole->permissions()->count())->toEqual(2);

    $this->testUserRole->revokePermissionTo($models);

    expect($this->testUserRole->permissions()->count())->toEqual(0);
});

it('can determine that the user does not have a permission', function () {
    expect($this->testUser->hasPermissionTo('edit-articles'))->toBeFalse();
});

it('throws an exception when the permission does not exist', function () {
    expect(fn () => $this->testUser->hasPermissionTo('does-not-exist'))
        ->toThrow(PermissionDoesNotExist::class);
});

it('throws an exception when the permission does not exist for this guard', function () {
    expect(fn () => $this->testUser->hasPermissionTo('does-not-exist', 'web'))
        ->toThrow(PermissionDoesNotExist::class);
});

it('can reject a user that does not have any permissions at all', function () {
    $user = new User;

    expect($user->hasPermissionTo('edit-articles'))->toBeFalse();
});

it('can determine that the user has any of the permissions directly', function () {
    expect($this->testUser->hasAnyPermission('edit-articles'))->toBeFalse();

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->testUser->hasAnyPermission('edit-news', 'edit-articles'))->toBeTrue();

    $this->testUser->givePermissionTo('edit-news');

    $this->testUser->revokePermissionTo($this->testUserPermission);

    expect($this->testUser->hasAnyPermission('edit-articles', 'edit-news'))->toBeTrue();
    expect($this->testUser->hasAnyPermission('edit-blog', 'Edit News', ['Edit News']))->toBeFalse();
});

it('can determine that the user has any of the permissions directly using an array', function () {
    expect($this->testUser->hasAnyPermission(['edit-articles']))->toBeFalse();

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']))->toBeTrue();

    $this->testUser->givePermissionTo('edit-news');

    $this->testUser->revokePermissionTo($this->testUserPermission);

    expect($this->testUser->hasAnyPermission(['edit-articles', 'edit-news']))->toBeTrue();
});

it('can determine that the user has any of the permissions via role', function () {
    $this->testUserRole->givePermissionTo('edit-articles');

    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasAnyPermission('edit-news', 'edit-articles'))->toBeTrue();
    expect($this->testUser->hasAnyPermission('edit-blog', 'Edit News', ['Edit News']))->toBeFalse();
});

it('can determine that the user has all of the permissions directly', function () {
    $this->testUser->givePermissionTo('edit-articles', 'edit-news');

    expect($this->testUser->hasAllPermissions('edit-articles', 'edit-news'))->toBeTrue();

    $this->testUser->revokePermissionTo('edit-articles');

    expect($this->testUser->hasAllPermissions('edit-articles', 'edit-news'))->toBeFalse();
    expect($this->testUser->hasAllPermissions(['edit-articles', 'edit-news'], 'edit-blog'))->toBeFalse();
});

it('can determine that the user has all of the permissions directly using an array', function () {
    expect($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']))->toBeFalse();

    $this->testUser->revokePermissionTo('edit-articles');

    expect($this->testUser->hasAllPermissions(['edit-news', 'edit-articles']))->toBeFalse();

    $this->testUser->givePermissionTo('edit-news');

    $this->testUser->revokePermissionTo($this->testUserPermission);

    expect($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']))->toBeFalse();
});

it('can determine that the user has all of the permissions via role', function () {
    $this->testUserRole->givePermissionTo('edit-articles', 'edit-news');

    $this->testUser->assignRole('testRole');

    expect($this->testUser->hasAllPermissions('edit-articles', 'edit-news'))->toBeTrue();
});

it('can determine that user has direct permission', function () {
    $this->testUser->givePermissionTo('edit-articles');
    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeTrue();
    expect($this->testUser->getDirectPermissions()->pluck('name'))->toEqual(collect(['edit-articles']));

    $this->testUser->revokePermissionTo('edit-articles');
    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeFalse();

    $this->testUser->assignRole('testRole');
    $this->testUserRole->givePermissionTo('edit-articles');
    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeFalse();
});

it('can list all the permissions via roles of user', function () {
    $roleModel = app(Role::class);
    $roleModel->findByName('testRole2')->givePermissionTo('edit-news');

    $this->testUserRole->givePermissionTo('edit-articles');
    $this->testUser->assignRole('testRole', 'testRole2');

    expect($this->testUser->getPermissionsViaRoles()->pluck('name')->sort()->values())
        ->toEqual(collect(['edit-articles', 'edit-news']));
});

it('can list all the coupled permissions both directly and via roles', function () {
    $this->testUser->givePermissionTo('edit-news');

    $this->testUserRole->givePermissionTo('edit-articles');
    $this->testUser->assignRole('testRole');

    expect($this->testUser->getAllPermissions()->pluck('name')->sort()->values())
        ->toEqual(collect(['edit-articles', 'edit-news']));
});

it('can sync multiple permissions', function () {
    $this->testUser->givePermissionTo('edit-news');

    $this->testUser->syncPermissions('edit-articles', 'edit-blog');

    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-blog'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-news'))->toBeFalse();
});

it('can avoid sync duplicated permissions', function () {
    $this->testUser->syncPermissions('edit-articles', 'edit-blog', 'edit-blog');

    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-blog'))->toBeTrue();
});

it('can avoid detach on permission that does not exist sync', function () {
    $this->testUser->syncPermissions('edit-articles');

    expect(fn () => $this->testUser->syncPermissions('permission-does-not-exist'))
        ->toThrow(PermissionDoesNotExist::class);

    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeTrue();
    expect($this->testUser->checkPermissionTo('permission-does-not-exist'))->toBeFalse();
});

it('can sync multiple permissions by id', function () {
    $this->testUser->givePermissionTo('edit-news');

    $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-blog'])->pluck($this->testUserPermission->getKeyName());

    $this->testUser->syncPermissions($ids);

    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-blog'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-news'))->toBeFalse();
});

it('sync permission ignores null inputs', function () {
    $this->testUser->givePermissionTo('edit-news');

    $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-blog'])->pluck($this->testUserPermission->getKeyName());

    $ids->push(null);

    $this->testUser->syncPermissions($ids);

    expect($this->testUser->hasDirectPermission('edit-articles'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-blog'))->toBeTrue();
    expect($this->testUser->hasDirectPermission('edit-news'))->toBeFalse();
});

it('sync permission error does not detach permissions', function () {
    $this->testUser->givePermissionTo('edit-news');

    expect(fn () => $this->testUser->syncPermissions('edit-articles', 'permission-that-does-not-exist'))
        ->toThrow(PermissionDoesNotExist::class);

    expect($this->testUser->fresh()->hasDirectPermission('edit-news'))->toBeTrue();
});

it('does not remove already associated permissions when assigning new permissions', function () {
    $this->testUser->givePermissionTo('edit-news');

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->testUser->fresh()->hasDirectPermission('edit-news'))->toBeTrue();
});

it('does not throw an exception when assigning a permission that is already assigned', function () {
    $this->testUser->givePermissionTo('edit-news');

    $this->testUser->givePermissionTo('edit-news');

    expect($this->testUser->fresh()->hasDirectPermission('edit-news'))->toBeTrue();
});

it('can sync permissions to a model that is not persisted', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->syncPermissions('edit-articles');
    $user->save();
    $user->save(); // test save same model twice

    expect($user->hasPermissionTo('edit-articles'))->toBeTrue();

    $user->syncPermissions('edit-articles');
    expect($user->hasPermissionTo('edit-articles'))->toBeTrue();
    expect($user->fresh()->hasPermissionTo('edit-articles'))->toBeTrue();
});

it('does not run unnecessary sqls when assigning new permissions', function () {
    $permission2 = app(Permission::class)->where('name', ['edit-news'])->first();

    DB::enableQueryLog();
    $this->testUser->syncPermissions($this->testUserPermission, $permission2);
    DB::disableQueryLog();

    $necessaryQueriesCount = 2;

    expect(DB::getQueryLog())->toHaveCount($necessaryQueriesCount);
});

it('calling givePermissionTo before saving object doesnt interfere with other objects', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->givePermissionTo('edit-news');
    $user->save();

    $user2 = new User(['email' => 'test2@user.com']);
    $user2->givePermissionTo('edit-articles');

    DB::enableQueryLog();
    $user2->save();
    DB::disableQueryLog();

    expect($user->fresh()->hasPermissionTo('edit-news'))->toBeTrue();
    expect($user->fresh()->hasPermissionTo('edit-articles'))->toBeFalse();

    expect($user2->fresh()->hasPermissionTo('edit-articles'))->toBeTrue();
    expect($user2->fresh()->hasPermissionTo('edit-news'))->toBeFalse();
    expect(count(DB::getQueryLog()))->toBe(2); // avoid unnecessary sync
});

it('calling syncPermissions before saving object doesnt interfere with other objects', function () {
    $user = new User(['email' => 'test@user.com']);
    $user->syncPermissions('edit-news');
    $user->save();

    $user2 = new User(['email' => 'test2@user.com']);
    $user2->syncPermissions('edit-articles');

    DB::enableQueryLog();
    $user2->save();
    DB::disableQueryLog();

    expect($user->fresh()->hasPermissionTo('edit-news'))->toBeTrue();
    expect($user->fresh()->hasPermissionTo('edit-articles'))->toBeFalse();

    expect($user2->fresh()->hasPermissionTo('edit-articles'))->toBeTrue();
    expect($user2->fresh()->hasPermissionTo('edit-news'))->toBeFalse();
    expect(count(DB::getQueryLog()))->toBe(2); // avoid unnecessary sync
});

it('can retrieve permission names', function () {
    $this->testUser->givePermissionTo('edit-news', 'edit-articles');
    expect($this->testUser->getPermissionNames()->sort()->values())
        ->toEqual(collect(['edit-articles', 'edit-news']));
});

it('can check many direct permissions', function () {
    $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);
    expect($this->testUser->hasAllDirectPermissions(['edit-news', 'edit-articles']))->toBeTrue();
    expect($this->testUser->hasAllDirectPermissions('edit-news', 'edit-articles'))->toBeTrue();
    expect($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news', 'edit-blog']))->toBeFalse();
    expect($this->testUser->hasAllDirectPermissions(['edit-articles', 'edit-news'], 'edit-blog'))->toBeFalse();
});

it('can check if there is any of the direct permissions given', function () {
    $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);
    expect($this->testUser->hasAnyDirectPermission(['edit-news', 'edit-blog']))->toBeTrue();
    expect($this->testUser->hasAnyDirectPermission('edit-news', 'edit-blog'))->toBeTrue();
    expect($this->testUser->hasAnyDirectPermission('edit-blog', 'Edit News', ['Edit News']))->toBeFalse();
});

it('can check permission based on logged in user guard', function () {
    $this->testUser->givePermissionTo(app(Permission::class)::create([
        'name' => 'do_that',
        'guard_name' => 'api',
    ]));
    $response = $this->actingAs($this->testUser, 'api')
        ->json('GET', '/check-api-guard-permission');
    $response->assertJson([
        'status' => true,
    ]);
});

it('can reject permission based on logged in user guard', function () {
    $unassignedPermission = app(Permission::class)::create([
        'name' => 'do_that',
        'guard_name' => 'api',
    ]);

    $assignedPermission = app(Permission::class)::create([
        'name' => 'do_that',
        'guard_name' => 'web',
    ]);

    $this->testUser->givePermissionTo($assignedPermission);
    $response = $this->withExceptionHandling()
        ->actingAs($this->testUser, 'api')
        ->json('GET', '/check-api-guard-permission');
    $response->assertJson([
        'status' => false,
    ]);
});

it('fires an event when a permission is added', function () {
    Event::fake();
    app('config')->set('permission.events_enabled', true);

    $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);

    $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])
        ->pluck($this->testUserPermission->getKeyName())
        ->toArray();

    Event::assertDispatched(PermissionAttachedEvent::class, function ($event) use ($ids) {
        return $event->model instanceof User
            && $event->model->hasPermissionTo('edit-news')
            && $event->model->hasPermissionTo('edit-articles')
            && $ids === $event->permissionsOrIds;
    });
});

it('does not fire an event when events are not enabled', function () {
    Event::fake();
    app('config')->set('permission.events_enabled', false);

    $this->testUser->givePermissionTo(['edit-articles', 'edit-news']);

    $ids = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])
        ->pluck($this->testUserPermission->getKeyName())
        ->toArray();

    Event::assertNotDispatched(PermissionAttachedEvent::class);
});

it('fires an event when a permission is removed', function () {
    Event::fake();
    app('config')->set('permission.events_enabled', true);

    $permissions = app(Permission::class)::whereIn('name', ['edit-articles', 'edit-news'])->get();

    $this->testUser->givePermissionTo($permissions);

    $this->testUser->revokePermissionTo($permissions);

    Event::assertDispatched(PermissionDetachedEvent::class, function ($event) use ($permissions) {
        return $event->model instanceof User
            && ! $event->model->hasPermissionTo('edit-news')
            && ! $event->model->hasPermissionTo('edit-articles')
            && $event->permissionsOrIds === $permissions;
    });
});

it('can be given a permission on role when lazy loading is restricted', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();

    $testRole = app(Role::class)->with('permissions')->get()->first();

    $testRole->givePermissionTo('edit-articles');

    expect($testRole->hasPermissionTo('edit-articles'))->toBeTrue();
});

it('can be given a permission on user when lazy loading is restricted', function () {
    expect(Model::preventsLazyLoading())->toBeTrue();

    User::create(['email' => 'other@user.com']);
    $testUser = User::with('permissions')->get()->first();

    $testUser->givePermissionTo('edit-articles');

    expect($testUser->hasPermissionTo('edit-articles'))->toBeTrue();
});
