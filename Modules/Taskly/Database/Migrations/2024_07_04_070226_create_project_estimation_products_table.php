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
        Schema::create('project_estimation_products', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('project_estimation_id')->nullable();
			$table->unsignedBigInteger('group_id')->nullable();
			$table->string("name")->nullable();
			$table->string("comment")->nullable();
			$table->string("type")->nullable();
            $table->longText("description")->nullable();
            $table->string("pos")->nullable();
			$table->integer('position')->nullable();
            $table->string("unit")->nullable();
            $table->double("quantity",10,3)->nullable();
			$table->tinyInteger('is_optional')->default(0);
            $table->string("campare_percent")->nullable();
			$table->longText('ai_description')->nullable();
			$table->longText('smart_template_data')->nullable();
            $table->timestamps();

			$table->foreign('project_estimation_id')->references('id')->on('project_estimations')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('estimation_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_estimation_products');
    }
};
