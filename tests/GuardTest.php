<?php

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Guard;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

it('returns null for the model of a guard that has no provider configured', function () {
    config()->set('auth.guards.no-provider-guard', []);

    expect(Guard::getModelForGuard('no-provider-guard'))->toBeNull();
});

it('resolves the model for a guard using an ldap provider', function () {
    config()->set('auth.guards.ldap-guard', ['provider' => 'ldap-provider']);
    config()->set('auth.providers.ldap-provider', [
        'driver' => 'ldap',
        'database' => ['model' => User::class],
    ]);

    expect(Guard::getModelForGuard('ldap-guard'))->toBe(User::class);
});

it('returns null from getPassportClient when no passport guards are configured', function () {
    expect(Guard::getPassportClient('web'))->toBeNull();
});

it('returns null from getPassportClient when the resolved guard does not support clients', function () {
    config()->set('auth.guards.fake-passport', ['driver' => 'passport', 'provider' => 'users']);

    Auth::shouldReceive('guard')->once()->with('fake-passport')->andReturn(new stdClass);

    expect(Guard::getPassportClient('web'))->toBeNull();
});
