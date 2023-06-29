<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('permission.table_names.model_has_blocked_permissions') ?? 'model_has_blocked_permissions';

        Schema::create($tableName, function (Blueprint $table) {
            $table->bigIncrements('permission_test_id');
            $table->morphs('model');
        });
    }

    public function down(): void
    {
        $tableName = config('permission.table_names.model_has_blocked_permissions') ?? 'model_has_blocked_permissions';

        Schema::drop($tableName);
    }
};
