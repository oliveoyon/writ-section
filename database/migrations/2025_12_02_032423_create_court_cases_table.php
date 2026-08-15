<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();

            // Linked to lawyers table; nullable for staff-created/legacy filings.
            $table->foreignId('lawyer_id')
                ->nullable()
                ->constrained('lawyers')
                ->nullOnDelete();

            // basic case info
            $table->string('case_type')->nullable();
            $table->text('description')->nullable();

            // status workflow
            $table->string('status')->default('draft');

            // temporary barcode workflow
            $table->string('temporary_barcode')->unique()->nullable();
            $table->timestamp('temporary_barcode_generated_at')->nullable();

            // section verification
            $table->timestamp('section_verified_at')->nullable();
            $table->unsignedBigInteger('section_verified_by')->nullable();

            // final case number
            $table->string('final_case_number')->nullable();
            $table->string('final_case_year')->nullable();

            $table->timestamps();
        });

        // Verified by a section user.
        Schema::table('cases', function (Blueprint $table) {
            $table->foreign('section_verified_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
