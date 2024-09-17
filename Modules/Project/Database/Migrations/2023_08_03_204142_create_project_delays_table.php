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
        Schema::create('project_delays', function (Blueprint $table) {
            $table->id();
            $table->morphs('creator');
            $table->foreignId('project_id')->nullable()->constrained();
            $table->text('reason')->nullable();
            $table->string('delay_in_weeks')->nullable();
            $table->date('new_deadline')->nullable();
            $table->text('media')->nullable();
            $table->text('internal_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_delays');
    }
};
