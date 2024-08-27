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
        Schema::create('content_template_langs', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('parent_id')->nullable();
            $table->string('lang', 100)->nullable();
            $table->longText('content')->nullable();
            $table->longText('variables')->nullable();
            $table->integer('created_by')->nullable();
			$table->integer('workspace')->nullable();
            $table->timestamps();

			$table->foreign('parent_id')->references('id')->on('contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_template_langs');
    }
};
