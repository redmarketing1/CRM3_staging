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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
			$table->string('provider')->nullable();
			$table->string('model')->nullable();
			$table->string('model_label')->nullable();
			$table->string('max_tokens')->nullable();
			$table->tinyInteger('status')->default(1)->comment('0 = Deactive, 1 = Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
