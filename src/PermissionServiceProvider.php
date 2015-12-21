<?php

namespace Spatie\Permission;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param PermissionRegistrar $permissionLoader
     */
    public function boot(PermissionRegistrar $permissionLoader)
    {
        $this->publishes([
            __DIR__.'/../resources/config/laravel-permission.php' => $this->app->configPath().'/'.'laravel-permission.php',
        ], 'config');

        if (!class_exists('CreatePermissionTables')) {
            // Publish the migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../resources/migrations/create_permission_tables.php.stub' => $this->app->databasePath().'/migrations/'.$timestamp.'_create_permission_tables.php',
            ], 'migrations');
        }

        $permissionLoader->registerPermissions();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/laravel-permission.php', 'laravel-permission');

        $this->registerModelBindings();

        $this->registerBladeExtensions();
    }

    /**
     * Bind the Permission and Role model into the IoC.
     */
    protected function registerModelBindings()
    {
        $config = $this->app->config['laravel-permission.models'];

        $this->app->bind(PermissionContract::class, $config['permission']);
        $this->app->bind(RoleContract::class, $config['role']);
    }

    /**
     * Register the blade extensions.
     */
    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {

            $bladeCompiler->directive('role', function ($role) {
                return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasrole', function ($role) {
                return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endhasrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ($roles) {
                return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ($roles) {
                return "<?php if(auth()->check() && auth()->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });
        });
    }
}
