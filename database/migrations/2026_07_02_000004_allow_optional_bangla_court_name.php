<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->string('name_bn')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable to avoid failing rollback when existing courts have no Bangla name.
    }
};
