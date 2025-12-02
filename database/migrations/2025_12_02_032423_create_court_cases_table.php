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

            // linked to lawyers table
            $table->foreignId('lawyer_id')
                ->constrained('lawyers')
                ->cascadeOnDelete();

            // basic case info
            $table->string('case_type')->nullable();
            $table->string('subject')->nullable();
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

        // add foreign key to admin_users
        Schema::table('cases', function (Blueprint $table) {
            $table->foreign('section_verified_by')
                ->references('id')
                ->on('admin_users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
