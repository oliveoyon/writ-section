<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('current_holder_at');
            $table->foreignId('returned_by_user_id')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
            $table->text('return_reason')->nullable()->after('returned_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('returned_by_user_id');
            $table->dropColumn('returned_at');
            $table->dropColumn('return_reason');
        });
    }
};
