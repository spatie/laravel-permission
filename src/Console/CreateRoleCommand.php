<?php

namespace Spatie\Permission\Console;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class CreateRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->ask('Input role name');

        try {
            app(Role::class)->findByName($name);

            return $this->error('Role already exists');
        } catch (RoleDoesNotExist $e) {
            // valid block
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        app(Role::class)->forceFill([
            'name' => $name
        ])->save();

        $this->info('Success!');
    }
}
