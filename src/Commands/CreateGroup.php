<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Group;
use Spatie\Permission\Contracts\Group as GroupContract;

class CreateGroup extends Command
{
    protected $signature = 'permission:create-group
        {name : The name of the group}
        {guard? : The name of the guard}';

    protected $description = 'Create a group';

    public function handle()
    {
        $groupClass = app(GroupContract::class);

        $group = $groupClass::create([
            'name' => $this->argument('name'),
            'guard_name' => $this->argument('guard'),
        ]);

        $this->info("Group `{$group->name}` created");
    }
}
