<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_dispatch_batches', function (Blueprint $table) {
            $table->dropForeign(['court_id']);
            $table->foreign('court_id')
                ->references('id')
                ->on('courts')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('court_dispatch_batches', function (Blueprint $table) {
            $table->dropForeign(['court_id']);
            $table->foreign('court_id')
                ->references('id')
                ->on('courts')
                ->cascadeOnDelete();
        });
    }
};
