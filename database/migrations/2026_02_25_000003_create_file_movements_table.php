<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('barcode_scanned');
            $table->string('from_section')->nullable();
            $table->string('to_section');
            $table->string('movement_type')->default('receive'); // receive, reject, override_receive
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->timestamps();

            $table->index(['case_id', 'received_at']);
            $table->index(['to_section', 'received_at']);
            $table->index('barcode_scanned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_movements');
    }
};
