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
        if (!Schema::hasTable('customer_debit_notes')) {
            Schema::create('customer_debit_notes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('bill')->default('0');
                $table->integer('vendor')->default('0');
                $table->decimal('amount', 15, 2)->default('0.00');
                $table->date('date');
                $table->integer('status')->default('0');
                $table->text('description')->nullable();
                $table->timestamps();

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_debit_notes');
    }
};
