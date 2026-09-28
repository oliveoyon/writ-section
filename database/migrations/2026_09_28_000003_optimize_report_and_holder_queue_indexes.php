<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->index(
                ['current_holder_user_id', 'status', 'current_holder_at', 'id'],
                'cases_holder_status_time_id_index'
            );
            $table->dropIndex('cases_holder_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->index(['current_holder_user_id', 'status'], 'cases_holder_status_index');
            $table->dropIndex('cases_holder_status_time_id_index');
        });
    }
};
