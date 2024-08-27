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
        Schema::create('estimation_groups', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('parent_id')->nullable();
			$table->unsignedBigInteger('estimation_id')->nullable();
			$table->string('group_pos')->nullable();
			$table->string('group_name')->nullable();
			$table->integer('position')->default('0');
            $table->timestamps();

			$table->foreign('parent_id')->references('id')->on('estimation_groups')->onDelete('cascade');
			$table->foreign('estimation_id')->references('id')->on('project_estimations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimation_groups');
    }
};
