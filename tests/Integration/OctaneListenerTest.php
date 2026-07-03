<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Octane\Events\RequestTerminated;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\PermissionServiceProvider;

/**
 * PermissionServiceProvider::registerOctaneListener() bails out early when
 * running in console. Pest/Testbench always runs under the CLI SAPI,
 * so runningInConsole() would otherwise always be true. Here we force it false
 * (it's memoized on Application, checked before falling back to PHP_SAPI)
 * so the real registration code path actually runs, then invoke it directly
 * since it's protected and normally only called from packageBooted().
 */
function registerOctaneListenerForTest(): void
{
    $app = app();

    $isRunningInConsole = new ReflectionProperty($app, 'isRunningInConsole');
    $isRunningInConsole->setAccessible(true);
    $isRunningInConsole->setValue($app, false);

    $registerOctaneListener = new ReflectionMethod(PermissionServiceProvider::class, 'registerOctaneListener');
    $registerOctaneListener->setAccessible(true);
    $registerOctaneListener->invoke(new PermissionServiceProvider($app));
}

/**
 * A clone of the base app, with its own PermissionRegistrar singleton, standing
 * in for the per-request "sandbox" container Octane passes on OperationTerminated
 * events. Using a distinct instance (rather than reusing the base app for both
 * $event->app() and $event->sandbox()) lets the tests below prove the listener
 * really does act on the sandbox, not on the shared base app.
 */
function makeSandboxApp(): Application
{
    $sandbox = clone app();
    $sandbox->forgetInstance(PermissionRegistrar::class);

    return $sandbox;
}

function dispatchOctaneRequestTerminated(Application $sandbox): void
{
    event(new RequestTerminated(app(), $sandbox, Request::create('/'), new Response));
}

it('does not register octane listeners when octane.listeners is not enabled', function () {
    config(['octane.listeners' => false]);

    registerOctaneListenerForTest();

    $sandbox = makeSandboxApp();
    $sandbox->make(PermissionRegistrar::class)->setPermissionsTeamId(1);

    dispatchOctaneRequestTerminated($sandbox);

    expect($sandbox->make(PermissionRegistrar::class)->getPermissionsTeamId())->toBe(1);
});

it('resets the permissions team id on the sandbox instance, not the base app, when an octane operation terminates', function () {
    config(['octane.listeners' => true]);

    registerOctaneListenerForTest();

    $sandbox = makeSandboxApp();

    app(PermissionRegistrar::class)->setPermissionsTeamId(1);
    $sandbox->make(PermissionRegistrar::class)->setPermissionsTeamId(2);

    dispatchOctaneRequestTerminated($sandbox);

    // Sandbox instance is reset, matching Octane's OperationTerminated contract...
    expect($sandbox->make(PermissionRegistrar::class)->getPermissionsTeamId())->toBeNull();
    // ...while the base app's own instance is untouched, proving the listener
    // used $event->sandbox() rather than $event->app().
    expect(app(PermissionRegistrar::class)->getPermissionsTeamId())->toBe(1);
});

it('clears the loaded permissions collection on the sandbox instance when register_octane_reset_listener is enabled', function () {
    config([
        'octane.listeners' => true,
        'permission.register_octane_reset_listener' => true,
    ]);

    registerOctaneListenerForTest();

    $sandbox = makeSandboxApp();

    $permissions = new ReflectionProperty(PermissionRegistrar::class, 'permissions');
    $permissions->setAccessible(true);

    app(PermissionRegistrar::class)->getPermissions();
    $sandbox->make(PermissionRegistrar::class)->getPermissions();

    expect($permissions->getValue(app(PermissionRegistrar::class)))->not->toBeNull();
    expect($permissions->getValue($sandbox->make(PermissionRegistrar::class)))->not->toBeNull();

    dispatchOctaneRequestTerminated($sandbox);

    expect($permissions->getValue($sandbox->make(PermissionRegistrar::class)))->toBeNull();
    // Base app's collection survives, again proving $event->sandbox() is what's used.
    expect($permissions->getValue(app(PermissionRegistrar::class)))->not->toBeNull();
});

it('does not clear the loaded permissions collection on octane termination unless register_octane_reset_listener is enabled', function () {
    config([
        'octane.listeners' => true,
        'permission.register_octane_reset_listener' => false,
    ]);

    registerOctaneListenerForTest();

    $sandbox = makeSandboxApp();

    $permissions = new ReflectionProperty(PermissionRegistrar::class, 'permissions');
    $permissions->setAccessible(true);

    $sandbox->make(PermissionRegistrar::class)->getPermissions();
    expect($permissions->getValue($sandbox->make(PermissionRegistrar::class)))->not->toBeNull();

    dispatchOctaneRequestTerminated($sandbox);

    expect($permissions->getValue($sandbox->make(PermissionRegistrar::class)))->not->toBeNull();
});
