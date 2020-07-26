<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Guard;
use Spatie\Permission\Traits\AdaptiveCommandParams;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreatePermission extends Command
{
    use AdaptiveCommandParams;

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'permission:create-permission';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a permission';

    /**
     * The permission app.
     *
     * @var object
     */
    protected $permissionApp;

    /**
     * The command constructor.
     */
    public function __construct()
    {
        $this->permissionApp = app(PermissionContract::class);

        $this->registerCustomColumns($this->permissionApp);

        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $permission = $this->permissionApp::findOrCreate($this->getInputParams());

        $this->info("Permission `{$permission->name}` created");
    }

    /**
     * Get the command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge([
            ['name', InputArgument::REQUIRED, 'The name of the permission'],
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
            ['guard', null, InputOption::VALUE_OPTIONAL, 'The name of the guard', Guard::getDefaultName($this->permissionApp)],
        ], $this->optionalExtraFields);
    }
}
