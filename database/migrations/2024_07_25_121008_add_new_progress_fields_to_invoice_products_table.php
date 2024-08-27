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
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->string('item')->nullable()->after('product_id');
			$table->decimal('total_price',10,2)->nullable()->default(0)->after('price');
			$table->integer('progress')->nullable()->default(0)->after('total_price');
			$table->decimal('progress_amount',11,2)->nullable()->after('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->dropColumn('item');
			$table->dropColums('total_price');
			$table->dropColumn('progress');
			$table->dropColums('progress_amount');
        });
    }
};
