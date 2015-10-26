<?php

namespace Spatie\Permission;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Contracts\RoleContract;
use Spatie\Permission\Contracts\PermissionContract;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->configFile = __DIR__.'/../config/laravel-permission.php';
    }
    /**
     * Bootstrap the application services.
     *
     * @param PermissionRegistrar $permissionLoader
     */
    public function boot(PermissionRegistrar $permissionLoader)
    {
        // Publish the configuration file
        $this->publishes([$this->configFile => config_path('laravel-permission.php')], 'config');

        if (!class_exists('CreatePermissionTables') && $this->app['config']['laravel-permission.permission'] == Permission::class) {
            // Publish the permissions migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__.'/../resources/migrations/create_permission_tables.php.stub' => $this->app->basePath().'/'.'database/migrations/'.$timestamp.'_create_permission_tables.php',
            ], 'migrations');
        }

        if (!class_exists('CreateRoleTables') && $this->app['config']['laravel-permission.role'] == Role::class) {
            // Publish the roles migration
            $timestamp = date('Y_m_d_His', time() + 1);
            $this->publishes([
                __DIR__.'/../resources/migrations/create_role_tables.php.stub' => $this->app->basePath().'/'.'database/migrations/'.$timestamp.'_create_role_tables.php',
            ], 'migrations');
        }

        $permissionLoader->registerPermissions();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configFile, 'laravel-permission');

        $this->registerModels();

        $this->registerBladeExtensions();
    }

    /**
     * Register the Role and Permission contract bindings.
     */
    protected function registerModels()
    {
        $this->app->bind(RoleContract::class, function($app) {
            return new $app['config']['laravel-permission.role'];
        });

        $this->app->bind(PermissionContract::class, function($app) {
            return new $app['config']['laravel-permission.permission'];
        });
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
