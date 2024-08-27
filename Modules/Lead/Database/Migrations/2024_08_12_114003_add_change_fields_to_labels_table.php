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
        Schema::table('labels', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('pipeline_id');
			$table->string('font_color')->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropColumn('order');
			$table->dropColumn('font_color');
        });
    }
};
