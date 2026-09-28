<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->unique('final_case_number', 'cases_final_case_number_unique');
            $table->index(['current_holder_user_id', 'status'], 'cases_holder_status_index');
            $table->index(['current_section', 'status'], 'cases_section_status_index');
            $table->index('created_at', 'cases_created_at_index');
        });

        Schema::table('file_movements', function (Blueprint $table) {
            $table->index('received_at', 'file_movements_received_at_index');
            $table->index(['movement_type', 'received_at'], 'file_movements_type_received_index');
            $table->index(['from_section', 'received_at'], 'file_movements_from_received_index');
            $table->index(['case_id', 'id'], 'file_movements_case_id_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropIndex('file_movements_case_id_id_index');
            $table->dropIndex('file_movements_from_received_index');
            $table->dropIndex('file_movements_type_received_index');
            $table->dropIndex('file_movements_received_at_index');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex('cases_created_at_index');
            $table->dropIndex('cases_section_status_index');
            $table->dropIndex('cases_holder_status_index');
            $table->dropUnique('cases_final_case_number_unique');
        });
    }
};
