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
        Schema::table('c_m_s', function (Blueprint $table) {
            $table->string('title_en')->nullable();
            $table->string('title_fr')->nullable();
            $table->string('title_es')->nullable();
            $table->longText('description_en')->nullable();
            $table->longText('description_fr')->nullable();
            $table->longText('description_es')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('c_m_s', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_fr', 'title_es', 'description_en', 'description_fr', 'description_es']);
        });
    }
};
