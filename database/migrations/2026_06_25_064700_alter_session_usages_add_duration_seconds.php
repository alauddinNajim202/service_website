<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_usages', function (Blueprint $table) {
            $table->integer('duration_seconds')->default(0)->after('ended_at');
            $table->dropColumn('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('session_usages', function (Blueprint $table) {
            $table->dropColumn('duration_seconds');
            $table->boolean('is_completed')->default(false);
        });
    }
};
