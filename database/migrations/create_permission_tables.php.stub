<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $permissionsForeignKeyName = str_singular($tableNames['permissions']).'_id';
            $table->integer($permissionsForeignKeyName)->unsigned();
            $table->morphs('model');

            $table->foreign($permissionsForeignKeyName)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary([$permissionsForeignKeyName, 'model_id', 'model_type']);
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $rolesForeignKeyName = str_singular($tableNames['roles']).'_id';
            $table->integer($rolesForeignKeyName)->unsigned();
            $table->morphs('model');

            $table->foreign($rolesForeignKeyName)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$rolesForeignKeyName, 'model_id', 'model_type']);
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $permissionsForeignKeyName = str_singular($tableNames['permissions']).'_id';
            $rolesForeignKeyName = str_singular($tableNames['roles']).'_id';

            $table->integer($permissionsForeignKeyName)->unsigned();
            $table->integer($rolesForeignKeyName)->unsigned();

            $table->foreign($permissionsForeignKeyName)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($rolesForeignKeyName)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$permissionsForeignKeyName, $rolesForeignKeyName]);

            app('cache')->forget('spatie.permission.cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
