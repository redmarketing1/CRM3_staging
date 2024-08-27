<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_quote_items', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('estimate_quote_id')->nullable();
			$table->unsignedBigInteger('product_id')->nullable();
			$table->double("base_price",10,2)->nullable();
			$table->double("price",10,2)->nullable();
			$table->double("total_price",10,2)->nullable();
			$table->longText('all_results')->nullable();
			$table->longText('smart_template_data')->nullable();
            $table->timestamps();

			$table->foreign('estimate_quote_id')->references('id')->on('estimate_quotes')->onDelete('cascade');
			$table->foreign('product_id')->references('id')->on('project_estimation_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_quote_items');
    }
};
