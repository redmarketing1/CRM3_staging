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
        Schema::create('smart_templates', function (Blueprint $table) {
            $table->id();
			$table->string('title')->nullable();
            $table->tinyInteger('type')->default('0')->comment('0 = main response , 1 =  description');
            $table->integer('request_count')->default('0');
            $table->integer('outliner')->default('0');
            $table->string('result_operation')->nullable();
			$table->unsignedBigInteger('ai_model_id')->nullable();
			$table->unsignedBigInteger('extraction_ai_model_id')->nullable();
            $table->integer('created_by')->default('0');
			$table->integer('workspace')->nullable();
            $table->timestamps();

			$table->foreign('ai_model_id')->references('id')->on('ai_models')->onDelete('set null');
			$table->foreign('extraction_ai_model_id')->references('id')->on('ai_models')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_templates');
    }
};
