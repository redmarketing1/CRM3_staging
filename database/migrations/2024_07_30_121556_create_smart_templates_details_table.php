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
		Schema::create('smart_templates_details', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('prompt_id')->nullable();
            $table->string('prompt_title')->nullable();
            $table->string('prompt_slug')->nullable();
			$table->longText('prompt_desc')->nullable();
            $table->integer('created_by')->default('0');
            $table->timestamps();

			$table->foreign('template_id')->references('id')->on('smart_templates')->onDelete('cascade');
			$table->foreign('prompt_id')->references('id')->on('contents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_templates_details');
    }
};
