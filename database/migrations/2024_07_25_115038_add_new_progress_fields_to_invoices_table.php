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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('client')->nullable()->after('shipping_display');
			$table->unsignedBigInteger('project')->nullable()->after('client');
			$table->unsignedBigInteger('project_estimation_id')->nullable()->after('project');
			$table->string('tax')->nullable()->after('project_estimation_id');
			$table->float('discount')->nullable()->default(0)->after('tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
			$table->dropColumn('client');
			$table->dropColums('project');
			$table->dropColums('project_estimation_id');
			$table->dropColumn('tax');
			$table->dropColums('discount');
		});
    }
};
