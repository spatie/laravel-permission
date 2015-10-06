<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration
{
    protected $users;
    protected $roles;
    protected $permissions;
    protected $role_has_permissions;
    protected $user_has_roles;
    protected $user_has_permissions;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->users=config('laravel-permissions.tables.users');
        $this->roles=config('laravel-permissions.tables.roles');
        $this->permissions=config('laravel-permissions.tables.permissions');
        $this->role_has_permissions=config('laravel-permissions.tables.role_has_permissions');
        $this->user_has_roles=config('laravel-permissions.tables.user_has_roles');
        $this->user_has_permissions=config('laravel-permissions.tables.user_has_permissions');
        Schema::create($this->roles, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($this->permissions, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($this->user_has_permissions, function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('permission_id')->unsigned();

            $table->foreign('user_id')
                ->references('id')
                ->on($this->users)
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('id')
                ->on($this->permissions)
                ->onDelete('cascade');

            $table->primary(['user_id', 'permission_id']);
        });

        Schema::create($this->user_has_roles, function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('role_id')
                ->references('id')
                ->on($this->roles)
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on($this->users)
                ->onDelete('cascade');

            $table->primary(['role_id', 'user_id']);

            Schema::create($this->role_has_permissions, function (Blueprint $table) {
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($this->permissions)
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($this->roles)
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id']);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->role_has_permissions);
        Schema::drop($this->user_has_roles);
        Schema::drop($this->user_has_permissions);
        Schema::drop($this->roles);
        Schema::drop($this->permissions);
    }
}
