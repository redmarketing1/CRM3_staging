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
        Schema::create('project_estimations', function (Blueprint $table) {
            $table->id();
			$table->string('title')->nullable();
			$table->unsignedBigInteger('project_id')->nullable();
			$table->date('issue_date')->nullable();
			$table->longText('technical_description')->nullable();
			$table->integer('created_by')->default('0');
			$table->integer('status')->nullable()->default('0');
			$table->tinyInteger('is_active')->default(0)->nullable();
			$table->tinyInteger('init_status')->default(1);
            $table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_estimations');
    }
};
