<?php

namespace Spatie\Permission\Console;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class CreatePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permission';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->ask('Input permission name');

        try {
            app(Permission::class)->findByName($name);

            return $this->error('Permission already exists');
        } catch (PermissionDoesNotExist $e) {
            // valid block
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        app(Permission::class)->forceFill([
            'name' => $name
        ])->save();

        $this->info('Success!');
    }
}
