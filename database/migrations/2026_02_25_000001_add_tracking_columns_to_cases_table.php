<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('initiated_by_user_id')->nullable()->after('lawyer_id')->constrained('users')->nullOnDelete();
            $table->string('entry_source')->default('lawyer')->after('initiated_by_user_id');
            $table->string('permanent_barcode')->nullable()->unique()->after('temporary_barcode_generated_at');
            $table->timestamp('permanent_barcode_generated_at')->nullable()->after('permanent_barcode');
            $table->string('current_section')->nullable()->after('final_case_year');
            $table->foreignId('current_holder_user_id')->nullable()->after('current_section')->constrained('users')->nullOnDelete();
            $table->timestamp('current_holder_at')->nullable()->after('current_holder_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('initiated_by_user_id');
            $table->dropColumn('entry_source');
            $table->dropUnique(['permanent_barcode']);
            $table->dropColumn('permanent_barcode');
            $table->dropColumn('permanent_barcode_generated_at');
            $table->dropColumn('current_section');
            $table->dropConstrainedForeignId('current_holder_user_id');
            $table->dropColumn('current_holder_at');
        });
    }
};
