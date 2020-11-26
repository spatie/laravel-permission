<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Guard;
use Spatie\Permission\Traits\AdaptiveCommandParams;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateRole extends Command
{
    use AdaptiveCommandParams;

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'permission:create-role';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a role';

    /**
     * The role app.
     *
     * @var object
     */
    protected $roleApp;

    /**
     * The command constructor.
     */
    public function __construct()
    {
        $this->roleApp = app(RoleContract::class);

        $this->registerCustomColumns($this->roleApp);

        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $input = $this->getInputParams();
        $role = $this->roleApp::findOrCreate(Arr::except($input, ['permissions']));

        $role->givePermissionTo($this->makePermissions($this->option('permissions')));

        $this->info("Role `{$role->name}` created");
    }

    /**
     * Create permissions.
     *
     * @param string|null $string
     *
     * @return Illuminate\Support\Collection|null
     */
    protected function makePermissions($string = null)
    {
        if (empty($string)) {
            return;
        }

        $permissionApp = app(PermissionContract::class);
        $permissions = array_map('trim', explode('|', $string));
        $models = [];

        foreach ($permissions as $permission) {
            if (false === strpos($permission, ',')) {
                // This is the case where the user only enters the permission name
                // We will create the permission with the given name and the guard_name from the guard option

                $params = [
                    'name' => $permission,
                    'guard_name' => $this->option('guard'),
                ];
            } else {
                // This is the case where the user enters additional column information for permission
                // We need to analyze to get the column names and values

                $params = [];
                $splitParts = array_map('trim', explode(',', $permission));

                foreach ($splitParts as $permissionParams) {
                    $keyValuePair = array_map('trim', explode(':', $permissionParams));
                    $column = $keyValuePair[0];
                    $value = (isset($keyValuePair[1]) && $keyValuePair[1]) ? $keyValuePair[1] : null;

                    if ($column) {
                        $params[$column] = $value;
                    }
                }

                // Overwrite the guard_name parameter
                $params['guard_name'] = $this->option('guard');
            }

            if ($model = $permissionApp::findOrCreate($params)) {
                $models[] = $model;
            }
        }

        return collect($models);
    }

    /**
     * Get the command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge([
            ['name', InputArgument::REQUIRED, 'The name of the role'],
        ], $this->requiredExtraFields);
    }

    /**
     * Get the command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge([
            ['guard', null, InputOption::VALUE_OPTIONAL, 'The name of the guard', Guard::getDefaultName($this->roleApp)],
            ['permissions', null, InputOption::VALUE_OPTIONAL, 'A list of permissions to assign to the role (separated by |)'],
        ], $this->optionalExtraFields);
    }
}
