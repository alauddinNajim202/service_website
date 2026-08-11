<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('session_packages', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_fr')->nullable()->after('name_en');
            $table->string('name_es')->nullable()->after('name_fr');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_fr')->nullable()->after('description_en');
            $table->text('description_es')->nullable()->after('description_fr');
            $table->string('duration_en')->nullable()->after('duration');
            $table->string('duration_fr')->nullable()->after('duration_en');
            $table->string('duration_es')->nullable()->after('duration_fr');
            $table->string('badge_en')->nullable()->after('badge');
            $table->string('badge_fr')->nullable()->after('badge_en');
            $table->string('badge_es')->nullable()->after('badge_fr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_packages', function (Blueprint $table) {
            $table->dropColumn([
                'name_en',
                'name_fr',
                'name_es',
                'description_en',
                'description_fr',
                'description_es',
                'duration_en',
                'duration_fr',
                'duration_es',
                'badge_en',
                'badge_fr',
                'badge_es'
            ]);
        });
    }
};
