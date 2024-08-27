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
        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->string('file')->nullable();
            $table->unsignedBigInteger('comment_by')->nullable();
            $table->unsignedBigInteger('parent')->nullable();
			$table->unsignedBigInteger('project_id')->nullable();
            $table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('parent')->references('id')->on('project_comments')->onDelete('cascade');
			$table->foreign('comment_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_comments');
    }
};
