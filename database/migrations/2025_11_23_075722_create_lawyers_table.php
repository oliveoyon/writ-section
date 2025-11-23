<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // link to users table
            $table->string('bar_council_id')->unique();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('picture')->nullable(); // path to profile picture
            $table->date('barDateOfJoining')->nullable();
            $table->date('barDateOfEnrollment')->nullable();
            $table->string('barCourtType')->nullable();
            $table->string('status')->default('active'); // you can adjust default value

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyers');
    }
};
