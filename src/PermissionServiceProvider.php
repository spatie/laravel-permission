<?php

namespace Spatie\Permission;

use Composer\InstalledVersions;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Compilers\BladeCompiler;
use Laravel\Octane\Contracts\OperationTerminated;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;

use function Illuminate\Support\enum_value;

class PermissionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-permission')
            ->hasConfigFile('permission')
            ->hasMigrations(['create_permission_tables'])
            ->hasCommands([
                Commands\CacheResetCommand::class,
                Commands\CreateRoleCommand::class,
                Commands\CreatePermissionCommand::class,
                Commands\ShowCommand::class,
                Commands\UpgradeForTeamsCommand::class,
                Commands\AssignRoleCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->callAfterResolving('blade.compiler', fn (BladeCompiler $bladeCompiler) => $this->registerBladeExtensions($bladeCompiler));
    }

    public function packageBooted(): void
    {
        $this->registerMacroHelpers();
        $this->registerModelBindings();
        $this->registerOctaneListener();

        $this->callAfterResolving(Gate::class, function (Gate $gate, Application $app) {
            if ($this->app['config']->get('permission.register_permission_check_method')) {
                $permissionLoader = $app->get(PermissionRegistrar::class);
                $permissionLoader->clearPermissionsCollection();
                $permissionLoader->registerPermissions($gate);
            }
        });

        $this->app->singleton(PermissionRegistrar::class);
        $this->registerAbout();
    }

    public static function bladeMethodWrapper(string $method, mixed $role, ?string $guard = null): bool
    {
        return auth($guard)->check() && auth($guard)->user()->{$method}($role);
    }

    protected function registerBladeExtensions(BladeCompiler $bladeCompiler): void
    {
        $bladeMethodWrapper = '\\Spatie\\Permission\\PermissionServiceProvider::bladeMethodWrapper';

        // permission checks
        $bladeCompiler->if('haspermission', fn () => $bladeMethodWrapper('checkPermissionTo', ...func_get_args()));

        // role checks
        $bladeCompiler->if('role', fn () => $bladeMethodWrapper('hasRole', ...func_get_args()));
        $bladeCompiler->if('hasrole', fn () => $bladeMethodWrapper('hasRole', ...func_get_args()));
        $bladeCompiler->if('hasanyrole', fn () => $bladeMethodWrapper('hasAnyRole', ...func_get_args()));
        $bladeCompiler->if('hasallroles', fn () => $bladeMethodWrapper('hasAllRoles', ...func_get_args()));
        $bladeCompiler->if('hasexactroles', fn () => $bladeMethodWrapper('hasExactRoles', ...func_get_args()));
        $bladeCompiler->directive('endunlessrole', fn () => '<?php endif; ?>');
    }

    protected function registerModelBindings(): void
    {
        $this->app->bind(PermissionContract::class, fn ($app) => $app->make($app->config['permission.models.permission']));
        $this->app->bind(RoleContract::class, fn ($app) => $app->make($app->config['permission.models.role']));
    }

    protected function registerMacroHelpers(): void
    {
        Route::macro('role', function ($roles = []) {
            $roles = Arr::wrap($roles);
            $roles = array_map(fn ($role) => enum_value($role), $roles);

            /** @var Route $this */
            return $this->middleware('role:'.implode('|', $roles));
        });

        Route::macro('permission', function ($permissions = []) {
            $permissions = Arr::wrap($permissions);
            $permissions = array_map(fn ($permission) => enum_value($permission), $permissions);

            /** @var Route $this */
            return $this->middleware('permission:'.implode('|', $permissions));
        });

        Route::macro('roleOrPermission', function ($rolesOrPermissions = []) {
            $rolesOrPermissions = Arr::wrap($rolesOrPermissions);
            $rolesOrPermissions = array_map(fn ($item) => enum_value($item), $rolesOrPermissions);

            /** @var Route $this */
            return $this->middleware('role_or_permission:'.implode('|', $rolesOrPermissions));
        });
    }

    protected function registerOctaneListener(): void
    {
        if ($this->app->runningInConsole() || ! $this->app['config']->get('octane.listeners')) {
            return;
        }

        $dispatcher = $this->app[Dispatcher::class];
        // @phpstan-ignore-next-line
        $dispatcher->listen(function (OperationTerminated $event) {
            // @phpstan-ignore-next-line
            $event->sandbox->make(PermissionRegistrar::class)->setPermissionsTeamId(null);
        });

        if (! $this->app['config']->get('permission.register_octane_reset_listener')) {
            return;
        }
        // @phpstan-ignore-next-line
        $dispatcher->listen(function (OperationTerminated $event) {
            // @phpstan-ignore-next-line
            $event->sandbox->make(PermissionRegistrar::class)->clearPermissionsCollection();
        });
    }

    protected function registerAbout(): void
    {
        if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
            return;
        }

        // array format: 'Display Text' => 'boolean-config-key name'
        $features = [
            'Teams' => 'teams',
            'Wildcard-Permissions' => 'enable_wildcard_permission',
            'Octane-Listener' => 'register_octane_reset_listener',
            'Passport' => 'use_passport_client_credentials',
        ];

        $config = $this->app['config'];

        AboutCommand::add('Spatie Permissions', static fn () => [
            'Features Enabled' => collect($features)
                ->filter(fn (string $feature, string $name): bool => $config->get("permission.{$feature}"))
                ->keys()
                ->whenEmpty(fn (Collection $collection) => $collection->push('Default'))
                ->join(', '),
            'Version' => InstalledVersions::getPrettyVersion('spatie/laravel-permission'),
        ]);
    }
}
