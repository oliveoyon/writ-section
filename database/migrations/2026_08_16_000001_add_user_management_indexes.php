<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['user_type', 'is_active'], 'users_type_active_index');
            $table->index('name', 'users_name_index');
            $table->index('department', 'users_department_index');
        });

        Schema::table('lawyers', function (Blueprint $table) {
            $table->index('full_name', 'lawyers_full_name_index');
            $table->index('phone', 'lawyers_phone_index');
        });
    }

    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->dropIndex('lawyers_full_name_index');
            $table->dropIndex('lawyers_phone_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_type_active_index');
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_department_index');
        });
    }
};
