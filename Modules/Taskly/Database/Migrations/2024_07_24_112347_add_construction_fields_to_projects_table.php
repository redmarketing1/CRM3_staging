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
        Schema::table('projects', function (Blueprint $table) {
			$table->bigInteger('client')->nullable()->after('password');
            $table->bigInteger('construction_detail_id')->nullable()->after('client');
			$table->tinyInteger('is_same_invoice_address')->default(1)->after('construction_detail_id');
			$table->longText('technical_description')->nullable()->after('is_same_invoice_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('construction_detail_id');
			$table->dropColumn('is_same_invoice_address');
			$table->dropColumn('client');
			$table->dropColumn('technical_description');
        });
    }
};
