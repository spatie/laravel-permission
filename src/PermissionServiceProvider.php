<?php

namespace Spatie\Permission;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot(PermissionRegistrar $permissionLoader)
    {
        $this->publishes([
            __DIR__.'/../config/permission.php' => $this->app->configPath().'/permission.php',
        ], 'config');

        if (! class_exists('CreatePermissionTables')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_permission_tables.php.stub' => $this->app->databasePath()."/migrations/{$timestamp}_create_permission_tables.php",
            ], 'migrations');
        }

        $this->registerModelBindings();

        $permissionLoader->registerPermissions();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/permission.php',
            'permission'
        );

        $this->registerBladeExtensions();
    }

    protected function registerModelBindings()
    {
        $config = $this->app->config['permission.models'];

        $this->app->bind(PermissionContract::class, $config['permission']);
        $this->app->bind(RoleContract::class, $config['role']);
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('role', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endhasrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');
                $roles = $this->convertPipeToArray($roles);

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');
                $roles = $this->convertPipeToArray($roles);

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });
        });
    }

    public function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return $pipeString;
        }

        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return $pipeString;
        }

        $roleString = '['.implode($quoteCharacter.','.$quoteCharacter, explode('|', $pipeString)).']';

        return $roleString;
    }
}
