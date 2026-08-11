<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('session_packages', function (Blueprint $table) {
            $table->boolean('is_feature')->default(false)->after('status');
            $table->string('feature_text')->nullable()->after('is_feature');
        });
    }

    public function down(): void
    {
        Schema::table('session_packages', function (Blueprint $table) {
            $table->dropColumn(['is_feature', 'feature_text']);
        });
    }
};
