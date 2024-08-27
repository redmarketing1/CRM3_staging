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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
			$table->char('iso',2)->nullable();
            $table->string('capital_name',80)->nullable();
            $table->string('name',80)->nullable();
            $table->char('iso3',3)->nullable();
            $table->integer('numcode')->nullable();
            $table->integer('phonecode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
