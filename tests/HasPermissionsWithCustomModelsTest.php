<?php

namespace Spatie\Permission\Test;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class HasPermissionsWithCustomModelsTest extends HasPermissionsTest
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @test */
    public function it_can_use_custom_model_permission(): void
    {
        $this->assertSame(get_class($this->testUserPermission), Permission::class);
    }

    /** @test */
    public function it_can_use_custom_fields_from_cache(): void
    {
        DB::connection()->getSchemaBuilder()->table(config('permission.table_names.roles'), function ($table) {
            $table->string('type')->default('R');
        });
        DB::connection()->getSchemaBuilder()->table(config('permission.table_names.permissions'), function ($table) {
            $table->string('type')->default('P');
        });

        $this->testUserRole->givePermissionTo($this->testUserPermission);
        app(PermissionRegistrar::class)->getPermissions();

        DB::enableQueryLog();
        $this->assertSame('P', Permission::findByName('edit-articles')->type);
        $this->assertSame('R', Permission::findByName('edit-articles')->roles[0]->type);
        DB::disableQueryLog();

        $this->assertSame(0, count(DB::getQueryLog()));
    }
}
