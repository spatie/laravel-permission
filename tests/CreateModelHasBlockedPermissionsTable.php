<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        Schema::create('model_has_blocked_permissions', function (Blueprint $table) use ($pivotPermission){
            $table->bigIncrements('permission_test_id');
            $table->morphs('model');
        });
    }

    public function down(): void
    {
        Schema::drop('model_has_blocked_permissions');
    }
};
