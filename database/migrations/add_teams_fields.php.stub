<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        if (! $teams) {
            return;
        }
        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if (empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            if (! Schema::hasColumn($tableNames['roles'], $columnNames['team_foreign_key'])) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            if (! Schema::hasColumn($tableNames['model_has_permissions'], $columnNames['team_foreign_key'])) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->default('1');;
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->dropPrimary();
                $table->primary([$columnNames['team_foreign_key'], 'permission_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            if (! Schema::hasColumn($tableNames['model_has_roles'], $columnNames['team_foreign_key'])) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->default('1');;
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->dropPrimary();
                $table->primary([$columnNames['team_foreign_key'], 'role_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
