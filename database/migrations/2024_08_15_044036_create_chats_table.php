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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
			$table->longText('message')->nullable();
			$table->tinyInteger('type')->default(0)->comment('0 = Request, 1 = Response');
			$table->unsignedBigInteger('conversation_id')->nullable();
			$table->unsignedBigInteger('ai_model_id')->nullable();
			$table->tinyInteger('status')->default(0)->comment('0 = Response not generated, 1 = Response generated');
            $table->timestamps();

			$table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
			$table->foreign('ai_model_id')->references('id')->on('ai_models')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
