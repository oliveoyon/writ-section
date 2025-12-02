<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_petitioners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('case_id')
                ->constrained('cases')
                ->cascadeOnDelete();

            $table->string('name_or_organization');
            $table->string('represented_by')->nullable();
            $table->string('phone')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_petitioners');
    }
};
