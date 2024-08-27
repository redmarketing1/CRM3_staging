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
        Schema::create('project_progress_mains', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('estimation_id')->nullable();
			$table->unsignedBigInteger('project_id')->nullable();
			$table->unsignedBigInteger('user_id')->nullable();
            $table->string("name")->nullable();
			$table->text('comment')->nullable();
            $table->text("signature")->nullable();
            $table->timestamps();

			$table->foreign('estimation_id')->references('id')->on('project_estimations')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_progress_mains');
    }
};
