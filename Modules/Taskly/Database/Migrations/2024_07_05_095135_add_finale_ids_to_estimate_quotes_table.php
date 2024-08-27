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
        Schema::table('estimate_quotes', function (Blueprint $table) {
            $table->tinyInteger('final_for_client')->default(0)->after('is_official_final');
			$table->tinyInteger('final_for_sub_contractor')->default(0)->after('final_for_client');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimate_quotes', function (Blueprint $table) {
            $table->dropColumn('final_for_client');
			$table->dropColumn('final_for_sub_contractor');
        });
    }
};
