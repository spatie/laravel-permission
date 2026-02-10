<?php

use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\WildcardPermissionInvalidArgument;
use Spatie\Permission\Exceptions\WildcardPermissionNotProperlyFormatted;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Tests\TestSupport\TestModels\User;
use Spatie\Permission\Tests\TestSupport\TestModels\WildcardPermission;

it('can check wildcard permission', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission1 = Permission::create(['name' => 'articles.edit,view,create']);
    $permission2 = Permission::create(['name' => 'news.*']);
    $permission3 = Permission::create(['name' => 'posts.*']);

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('posts.create'))->toBeTrue();
    expect($user1->hasPermissionTo('posts.create.123'))->toBeTrue();
    expect($user1->hasPermissionTo('posts.*'))->toBeTrue();
    expect($user1->hasPermissionTo('articles.view'))->toBeTrue();
    expect($user1->hasPermissionTo('projects.view'))->toBeFalse();
});

it('can check wildcard permission for a non default guard', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission1 = Permission::create(['name' => 'articles.edit,view,create', 'guard_name' => 'api']);
    $permission2 = Permission::create(['name' => 'news.*', 'guard_name' => 'api']);
    $permission3 = Permission::create(['name' => 'posts.*', 'guard_name' => 'api']);

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('posts.create', 'api'))->toBeTrue();
    expect($user1->hasPermissionTo('posts.create.123', 'api'))->toBeTrue();
    expect($user1->hasPermissionTo('posts.*', 'api'))->toBeTrue();
    expect($user1->hasPermissionTo('articles.view', 'api'))->toBeTrue();
    expect($user1->hasPermissionTo('projects.view', 'api'))->toBeFalse();
});

it('can check wildcard permission from instance without explicit guard argument', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission2 = Permission::create(['name' => 'articles.view']);
    $permission1 = Permission::create(['name' => 'articles.edit', 'guard_name' => 'api']);
    $permission3 = Permission::create(['name' => 'news.*', 'guard_name' => 'api']);
    $permission4 = Permission::create(['name' => 'posts.*', 'guard_name' => 'api']);

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo($permission1))->toBeTrue();
    expect($user1->hasPermissionTo($permission2))->toBeTrue();
    expect($user1->hasPermissionTo($permission3))->toBeTrue();
    expect($user1->hasPermissionTo($permission4))->toBeFalse();
    expect($user1->hasPermissionTo('articles.edit'))->toBeFalse();
});

it('can assign wildcard permissions using enums', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $articlesCreator = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardArticlesCreator;
    $newsEverything = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardNewsEverything;
    $postsEverything = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardPostsEverything;
    $postsCreate = Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardPostsCreate;

    $permission1 = app(Permission::class)->findOrCreate($articlesCreator->value, 'web');
    $permission2 = app(Permission::class)->findOrCreate($newsEverything->value, 'web');
    $permission3 = app(Permission::class)->findOrCreate($postsEverything->value, 'web');

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo($postsCreate))->toBeTrue();
    expect($user1->hasPermissionTo($postsCreate->value.'.123'))->toBeTrue();
    expect($user1->hasPermissionTo($postsEverything))->toBeTrue();

    expect($user1->hasPermissionTo(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardArticlesView))->toBeTrue();
    expect($user1->hasAnyPermission(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardArticlesView))->toBeTrue();

    expect($user1->hasPermissionTo(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardProjectsView))->toBeFalse();

    $user1->revokePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardPostsCreate))->toBeFalse();
    expect($user1->hasPermissionTo($postsCreate->value.'.123'))->toBeFalse();
    expect($user1->hasPermissionTo(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardPostsEverything))->toBeFalse();

    expect($user1->hasPermissionTo(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardArticlesView))->toBeFalse();
    expect($user1->hasAnyPermission(Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum::WildcardArticlesView))->toBeFalse();
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('can check wildcard permissions via roles', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole('testRole');

    $permission1 = Permission::create(['name' => 'articles,projects.edit,view,create']);
    $permission2 = Permission::create(['name' => 'news.*.456']);
    $permission3 = Permission::create(['name' => 'posts']);

    $this->testUserRole->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('posts.create'))->toBeTrue();
    expect($user1->hasPermissionTo('news.create.456'))->toBeTrue();
    expect($user1->hasPermissionTo('projects.create'))->toBeTrue();
    expect($user1->hasPermissionTo('articles.view'))->toBeTrue();
    expect($user1->hasPermissionTo('articles.list'))->toBeFalse();
    expect($user1->hasPermissionTo('projects.list'))->toBeFalse();
});

it('can check custom wildcard permission', function () {
    app('config')->set('permission.enable_wildcard_permission', true);
    app('config')->set('permission.wildcard_permission', WildcardPermission::class);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission1 = Permission::create(['name' => 'articles:edit;view;create']);
    $permission2 = Permission::create(['name' => 'news:@']);
    $permission3 = Permission::create(['name' => 'posts:@']);

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('posts:create'))->toBeTrue();
    expect($user1->hasPermissionTo('posts:create:123'))->toBeTrue();
    expect($user1->hasPermissionTo('posts:@'))->toBeTrue();
    expect($user1->hasPermissionTo('articles:view'))->toBeTrue();
    expect($user1->hasPermissionTo('posts.*'))->toBeFalse();
    expect($user1->hasPermissionTo('articles.view'))->toBeFalse();
    expect($user1->hasPermissionTo('projects:view'))->toBeFalse();
});

it('can check custom wildcard permissions via roles', function () {
    app('config')->set('permission.enable_wildcard_permission', true);
    app('config')->set('permission.wildcard_permission', WildcardPermission::class);

    $user1 = User::create(['email' => 'user1@test.com']);

    $user1->assignRole('testRole');

    $permission1 = Permission::create(['name' => 'articles;projects:edit;view;create']);
    $permission2 = Permission::create(['name' => 'news:@:456']);
    $permission3 = Permission::create(['name' => 'posts']);

    $this->testUserRole->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('posts:create'))->toBeTrue();
    expect($user1->hasPermissionTo('news:create:456'))->toBeTrue();
    expect($user1->hasPermissionTo('projects:create'))->toBeTrue();
    expect($user1->hasPermissionTo('articles:view'))->toBeTrue();
    expect($user1->hasPermissionTo('news.create.456'))->toBeFalse();
    expect($user1->hasPermissionTo('projects.create'))->toBeFalse();
    expect($user1->hasPermissionTo('articles:list'))->toBeFalse();
    expect($user1->hasPermissionTo('projects:list'))->toBeFalse();
});

it('can check non wildcard permissions', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission1 = Permission::create(['name' => 'edit articles']);
    $permission2 = Permission::create(['name' => 'create news']);
    $permission3 = Permission::create(['name' => 'update comments']);

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('edit articles'))->toBeTrue();
    expect($user1->hasPermissionTo('create news'))->toBeTrue();
    expect($user1->hasPermissionTo('update comments'))->toBeTrue();
});

it('can verify complex wildcard permissions', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission1 = Permission::create(['name' => '*.create,update,delete.*.test,course,finance']);
    $permission2 = Permission::create(['name' => 'papers,posts,projects,orders.*.test,test1,test2.*']);
    $permission3 = Permission::create(['name' => 'User::class.create,edit,view']);

    $user1->givePermissionTo([$permission1, $permission2, $permission3]);

    expect($user1->hasPermissionTo('invoices.delete.367463.finance'))->toBeTrue();
    expect($user1->hasPermissionTo('projects.update.test2.test3'))->toBeTrue();
    expect($user1->hasPermissionTo('User::class.edit'))->toBeTrue();
    expect($user1->hasPermissionTo('User::class.delete'))->toBeFalse();
    expect($user1->hasPermissionTo('User::class.*'))->toBeFalse();
});

it('throws exception when wildcard permission is not properly formatted', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user1 = User::create(['email' => 'user1@test.com']);

    $permission = Permission::create(['name' => '*..']);

    $user1->givePermissionTo([$permission]);

    expect(fn () => $user1->hasPermissionTo('invoices.*'))
        ->toThrow(WildcardPermissionNotProperlyFormatted::class);
});

it('can verify permission instances not assigned to user', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    $userPermission = Permission::create(['name' => 'posts.*']);
    $permissionToVerify = Permission::create(['name' => 'posts.create']);

    $user->givePermissionTo([$userPermission]);

    expect($user->hasPermissionTo('posts.create'))->toBeTrue();
    expect($user->hasPermissionTo('posts.create.123'))->toBeTrue();
    expect($user->hasPermissionTo($permissionToVerify->id))->toBeTrue();
    expect($user->hasPermissionTo($permissionToVerify))->toBeTrue();
});

it('can verify permission instances assigned to user', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    $userPermission = Permission::create(['name' => 'posts.*']);
    $permissionToVerify = Permission::create(['name' => 'posts.create']);

    $user->givePermissionTo([$userPermission, $permissionToVerify]);

    expect($user->hasPermissionTo('posts.create'))->toBeTrue();
    expect($user->hasPermissionTo('posts.create.123'))->toBeTrue();
    expect($user->hasPermissionTo($permissionToVerify))->toBeTrue();
    expect($user->hasPermissionTo($userPermission))->toBeTrue();
});

it('can verify integers as strings', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    $userPermission = Permission::create(['name' => '8']);

    $user->givePermissionTo([$userPermission]);

    expect($user->hasPermissionTo('8'))->toBeTrue();
});

it('throws exception when permission has invalid arguments', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    expect(fn () => $user->hasPermissionTo(['posts.create']))
        ->toThrow(WildcardPermissionInvalidArgument::class);
});

it('throws exception when permission id not exists', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    expect(fn () => $user->hasPermissionTo(6))
        ->toThrow(PermissionDoesNotExist::class);
});
