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
        Schema::table('users', function (Blueprint $table) {
            $table->string('title')->nullable()->after('active_workspace');
            $table->string('salutation')->nullable()->after('title');
			$table->string('first_name')->nullable()->after('salutation');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
			$table->text('address_1')->nullable()->after('phone');
			$table->text('address_2')->nullable()->after('address_1');
			$table->string('city')->nullable()->after('address_2');
			$table->string('district_1')->nullable()->after('city');
			$table->string('district_2')->nullable()->after('district_1');
			$table->string('state')->nullable()->after('district_2');
			$table->string('country')->nullable()->after('state');
			$table->string('zip_code')->nullable()->after('country');
			$table->string('lat')->nullable()->after('zip_code');
            $table->string('long')->nullable()->after('lat');
			$table->string('company_name')->nullable()->after('long');
			$table->string('tax_number')->nullable()->after('company_name');
			$table->string('website')->nullable()->after('tax_number');
			$table->longText('notes')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('title');
			$table->dropColums('salutation');
			$table->dropColums('first_name');
			$table->dropColums('last_name');
            $table->dropColums('phone');
            $table->dropColums('address_1');
            $table->dropColums('address_2');
			$table->dropColums('city');
			$table->dropColums('district_1');
			$table->dropColums('district_2');
			$table->dropColums('state');
			$table->dropColums('country');
			$table->dropColums('zip_code');
			$table->dropColums('lat');
			$table->dropColums('long');
			$table->dropColums('company_name');
            $table->dropColums('tax_number');
            $table->dropColums('website');
			$table->dropColums('notes');
        });
    }
};
