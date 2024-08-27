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
        Schema::create('estimate_quotes', function (Blueprint $table) {
            $table->id();
			$table->string('title')->nullable();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->unsignedBigInteger('project_id')->nullable();
			$table->unsignedBigInteger('project_estimation_id')->nullable();
			$table->double("tax",10,2)->nullable();
			$table->double("discount",10,2)->nullable();
			$table->double("net",10,2)->nullable();
			$table->double("net_with_discount",10,2)->nullable();
			$table->double("gross",10,2)->nullable();
			$table->double("gross_with_discount",10,2)->nullable();
			$table->double("markup",10,2)->nullable()->default(0);
			$table->tinyInteger('is_display')->default(1);
			$table->tinyInteger('is_clone')->default(0);
			$table->tinyInteger('is_final')->default(0);
			$table->tinyInteger('is_official_final')->default(0);
			$table->tinyInteger('is_ai')->default(0);
			$table->unsignedBigInteger('smart_template_id')->nullable();
            $table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('project_estimation_id')->references('id')->on('project_estimations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_quotes');
    }
};
