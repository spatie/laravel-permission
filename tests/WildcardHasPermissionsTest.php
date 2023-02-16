<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\WildcardPermissionInvalidArgument;
use Spatie\Permission\Exceptions\WildcardPermissionNotProperlyFormatted;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Tests\TestModels\User;
use Spatie\Permission\Tests\TestModels\WildcardPermission;

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

    $user1->hasPermissionTo('invoices.*');
})->throws(WildcardPermissionNotProperlyFormatted::class);

it('can verify permission instances not assigned to user', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    $userPermission = Permission::create(['name' => 'posts.*']);
    $permissionToVerify = Permission::create(['name' => 'posts.create']);

    $user->givePermissionTo([$userPermission]);

    expect($user->hasPermissionTo('posts.create'))->toBeTrue()
        ->and($user->hasPermissionTo('posts.create.123'))->toBeTrue()
        ->and($user->hasPermissionTo($permissionToVerify->id))->toBeTrue()
        ->and($user->hasPermissionTo($permissionToVerify))->toBeTrue();
});

it('can verify permission instances assigned to user', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    $userPermission = Permission::create(['name' => 'posts.*']);
    $permissionToVerify = Permission::create(['name' => 'posts.create']);

    $user->givePermissionTo([$userPermission, $permissionToVerify]);

    expect($user->hasPermissionTo('posts.create'))->toBeTrue()
        ->and($user->hasPermissionTo('posts.create.123'))->toBeTrue()
        ->and($user->hasPermissionTo($permissionToVerify))->toBeTrue()
        ->and($user->hasPermissionTo($userPermission))->toBeTrue();
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

    $user->hasPermissionTo(['posts.create']);
})->throws(WildcardPermissionInvalidArgument::class);

it('throws exception when permission id not exists', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $user = User::create(['email' => 'user@test.com']);

    $user->hasPermissionTo(6);
})->throws(PermissionDoesNotExist::class);
