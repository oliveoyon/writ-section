<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_dispatch_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('dispatch');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('received_by_name')->nullable();
            $table->string('received_by_designation')->nullable();
            $table->string('received_by_phone')->nullable();
            $table->string('handover_to_section')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('court_dispatch_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('court_dispatch_batches')->cascadeOnDelete();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('barcode_scanned');
            $table->string('from_section')->nullable();
            $table->string('to_section')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_dispatch_batch_items');
        Schema::dropIfExists('court_dispatch_batches');
    }
};
