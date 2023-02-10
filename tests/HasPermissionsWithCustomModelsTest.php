<?php

namespace Spatie\Permission\Test;

use DB;
use Spatie\Permission\PermissionRegistrar;

class HasPermissionsWithCustomModelsTest extends HasPermissionsTest
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @test */
    public function it_can_use_custom_model_permission()
    {
        $this->assertSame(get_class($this->testUserPermission), Permission::class);
    }

    /** @test */
    public function it_can_use_custom_fields_from_cache()
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

    /** @test */
    public function it_can_scope_users_using_a_int()
    {
        // Skipped because custom model uses uuid,
        // replacement "it_can_scope_users_using_a_uuid"
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_scope_users_using_a_uuid()
    {
        $uuid1 = $this->testUserPermission->getKey();
        $uuid2 = app(Permission::class)::where('name', 'edit-news')->first()->getKey();

        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->givePermissionTo([$uuid1, $uuid2]);
        $this->testUserRole->givePermissionTo($uuid1);
        $user2->assignRole('testRole');

        $scopedUsers1 = User::permission($uuid1)->get();
        $scopedUsers2 = User::permission([$uuid2])->get();

        $this->assertEquals(2, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
    }
}
