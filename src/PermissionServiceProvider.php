<?php

namespace Spatie\Permission;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();

        $this->registerMacroHelpers();

        $this->registerCommands();

        $this->registerModelBindings();

        $this->registerOctaneListener();

        $this->callAfterResolving(Gate::class, function (Gate $gate, Application $app) {
            if ($this->app['config']->get('permission.register_permission_check_method')) {
                /** @var PermissionRegistrar $permissionLoader */
                $permissionLoader = $app->get(PermissionRegistrar::class);
                $permissionLoader->clearPermissionsCollection();
                $permissionLoader->registerPermissions($gate);
            }
        });

        $this->app->singleton(PermissionRegistrar::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/permission.php',
            'permission'
        );

        $this->callAfterResolving('blade.compiler', fn (BladeCompiler $bladeCompiler) => $this->registerBladeExtensions($bladeCompiler));
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/permission.php' => config_path('permission.php'),
        ], 'permission-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_permission_tables.php.stub' => $this->getMigrationFileName('create_permission_tables.php'),
        ], 'permission-migrations');
    }

    protected function registerCommands(): void
    {
        $this->commands([
            Commands\CacheReset::class,
        ]);

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\CreateRole::class,
            Commands\CreatePermission::class,
            Commands\Show::class,
            Commands\UpgradeForTeams::class,
        ]);
    }

    protected function registerOctaneListener(): void
    {
        if ($this->app->runningInConsole() || ! $this->app['config']->get('octane.listeners')) {
            return;
        }

        $dispatcher = $this->app[Dispatcher::class];
        // @phpstan-ignore-next-line
        $dispatcher->listen(function (\Laravel\Octane\Events\OperationTerminated $event) {
            // @phpstan-ignore-next-line
            $event->sandbox->make(PermissionRegistrar::class)->setPermissionsTeamId(null);
        });

        if (! $this->app['config']->get('permission.register_octane_reset_listener')) {
            return;
        }
        // @phpstan-ignore-next-line
        $dispatcher->listen(function (\Laravel\Octane\Events\OperationTerminated $event) {
            // @phpstan-ignore-next-line
            $event->sandbox->make(PermissionRegistrar::class)->clearPermissionsCollection();
        });
    }

    protected function registerModelBindings(): void
    {
        $this->app->bind(PermissionContract::class, fn ($app) => $app->make($app->config['permission.models.permission']));
        $this->app->bind(RoleContract::class, fn ($app) => $app->make($app->config['permission.models.role']));
    }

    public static function bladeMethodWrapper($method, $role, $guard = null): bool
    {
        return auth($guard)->check() && auth($guard)->user()->{$method}($role);
    }

    protected function registerBladeExtensions($bladeCompiler): void
    {
        $bladeMethodWrapper = '\\Spatie\\Permission\\PermissionServiceProvider::bladeMethodWrapper';

        $bladeCompiler->directive('role', fn ($args) => "<?php if({$bladeMethodWrapper}('hasRole', {$args})): ?>");
        $bladeCompiler->directive('elserole', fn ($args) => "<?php elseif({$bladeMethodWrapper}('hasRole', {$args})): ?>");
        $bladeCompiler->directive('endrole', fn () => '<?php endif; ?>');

        $bladeCompiler->directive('haspermission', fn ($args) => "<?php if({$bladeMethodWrapper}('checkPermissionTo', {$args})): ?>");
        $bladeCompiler->directive('elsehaspermission', fn ($args) => "<?php elseif({$bladeMethodWrapper}('checkPermissionTo', {$args})): ?>");
        $bladeCompiler->directive('endhaspermission', fn () => '<?php endif; ?>');

        $bladeCompiler->directive('hasrole', fn ($args) => "<?php if({$bladeMethodWrapper}('hasRole', {$args})): ?>");
        $bladeCompiler->directive('endhasrole', fn () => '<?php endif; ?>');

        $bladeCompiler->directive('hasanyrole', fn ($args) => "<?php if({$bladeMethodWrapper}('hasAnyRole', {$args})): ?>");
        $bladeCompiler->directive('endhasanyrole', fn () => '<?php endif; ?>');

        $bladeCompiler->directive('hasallroles', fn ($args) => "<?php if({$bladeMethodWrapper}('hasAllRoles', {$args})): ?>");
        $bladeCompiler->directive('endhasallroles', fn () => '<?php endif; ?>');

        $bladeCompiler->directive('unlessrole', fn ($args) => "<?php if(! {$bladeMethodWrapper}('hasRole', {$args})): ?>");
        $bladeCompiler->directive('endunlessrole', fn () => '<?php endif; ?>');

        $bladeCompiler->directive('hasexactroles', fn ($args) => "<?php if({$bladeMethodWrapper}('hasExactRoles', {$args})): ?>");
        $bladeCompiler->directive('endhasexactroles', fn () => '<?php endif; ?>');
    }

    protected function registerMacroHelpers(): void
    {
        if (! method_exists(Route::class, 'macro')) { // Lumen
            return;
        }

        Route::macro('role', function ($roles = []) {
            /** @var Route $this */
            return $this->middleware('role:'.implode('|', Arr::wrap($roles)));
        });

        Route::macro('permission', function ($permissions = []) {
            /** @var Route $this */
            return $this->middleware('permission:'.implode('|', Arr::wrap($permissions)));
        });
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
