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
        Schema::create('smart_prompt_queues', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('project_id')->nullable();
			$table->unsignedBigInteger('estimation_id')->nullable();
			$table->unsignedBigInteger('quote_id')->nullable();
			$table->unsignedBigInteger('product_id')->nullable();
			$table->unsignedBigInteger('smart_template_id')->nullable();
			$table->string('smart_template_main_title')->nullable();
			$table->string('smart_template_name')->nullable();
			$table->string('smart_template_slug')->nullable();
			$table->unsignedBigInteger('prompt_id')->nullable();
			$table->longText('prompt')->nullable();
			$table->string('prompt_title')->nullable();
			$table->string('prompt_slug')->nullable();
			$table->integer('number_of_request')->default('0');
			$table->integer('outliner')->default('0');
			$table->string('result_operation')->nullable();
			$table->string("language")->nullable();
			$table->tinyInteger('type')->default(1)->comment('0 = main response , 1 =  number');
			$table->longText('result_description')->nullable();
			$table->longText('result_number')->nullable();
			$table->string('ai_model_name')->nullable();
			$table->string('ai_model_provider')->nullable();
			$table->string('extraction_ai_model_name')->nullable();
			$table->string('extraction_ai_model_provider')->nullable();
			$table->tinyInteger('status')->default(1)->comment('0 = not started, 1 = finished, 2 = quate_generated, 3 = error, 4 = cancel');
			$table->string('error_message')->nullable();
            $table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('estimation_id')->references('id')->on('project_estimations')->onDelete('cascade');
			$table->foreign('quote_id')->references('id')->on('estimate_quotes')->onDelete('cascade');
			$table->foreign('product_id')->references('id')->on('project_estimation_products')->onDelete('cascade');
			$table->foreign('smart_template_id')->references('id')->on('smart_templates')->onDelete('set null');
			$table->foreign('prompt_id')->references('id')->on('contents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_prompt_queues');
    }
};
