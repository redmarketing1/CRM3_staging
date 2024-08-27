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
        Schema::create('smart_prompt_queue_results', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('spq_id')->nullable();
			$table->longText('result_description')->nullable();
			$table->longText('result_number')->nullable();
            $table->timestamps();

			$table->foreign('spq_id')->references('id')->on('smart_prompt_queues')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_prompt_queue_results');
    }
};
