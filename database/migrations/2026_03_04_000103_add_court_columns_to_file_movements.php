<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            $table->foreignId('court_id')->nullable()->after('case_id')->constrained('courts')->nullOnDelete();
            $table->foreignId('court_dispatch_batch_id')->nullable()->after('court_id')->constrained('court_dispatch_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('court_dispatch_batch_id');
            $table->dropConstrainedForeignId('court_id');
        });
    }
};
