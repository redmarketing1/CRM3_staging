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
        Schema::create('project_progress', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('estimation_id')->nullable();
			$table->unsignedBigInteger('progress_id')->nullable();
			$table->unsignedBigInteger('product_id')->nullable();
			$table->double("progress",10,2)->nullable();
			$table->string("progress_amount")->nullable();
			$table->string("remarks")->nullable();
			$table->text("signature")->nullable();
			$table->datetime("approve_date")->nullable();
			$table->tinyInteger('status')->default(0);
            $table->timestamps();

			$table->foreign('estimation_id')->references('id')->on('project_estimations')->onDelete('cascade');
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
        Schema::dropIfExists('project_progress');
    }
};
