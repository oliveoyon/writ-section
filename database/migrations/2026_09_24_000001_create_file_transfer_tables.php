<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_transfer_batches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('batch_no', 32)->nullable()->unique();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sender_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('recipient_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('sender_name', 150);
            $table->string('sender_employee_id', 50)->nullable();
            $table->string('sender_section', 150)->nullable();
            $table->string('recipient_name', 150);
            $table->string('recipient_employee_id', 50)->nullable();
            $table->string('recipient_section', 150)->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamp('sent_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sender_user_id', 'status', 'sent_at']);
            $table->index(['recipient_user_id', 'status', 'sent_at']);
            $table->index(['status', 'sent_at']);
        });

        Schema::create('file_transfer_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('batch_id')->constrained('file_transfer_batches')->restrictOnDelete();
            $table->foreignId('case_id')->constrained('cases')->restrictOnDelete();
            $table->foreignId('active_case_id')->nullable()->unique()->constrained('cases')->restrictOnDelete();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamp('sent_at');
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('file_movement_id')->nullable()->constrained('file_movements')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'case_id']);
            $table->index(['batch_id', 'status', 'id']);
            $table->index(['case_id', 'status']);
            $table->index(['status', 'sent_at']);
            $table->index(['status', 'received_at']);
            $table->index(['received_by_user_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_transfer_items');
        Schema::dropIfExists('file_transfer_batches');
    }
};
