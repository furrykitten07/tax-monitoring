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
        Schema::table('tax_records', function (Blueprint $table) {
            $table->decimal('dpp_amount_other', 15, 2)->after('dpp_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_records', function (Blueprint $table) {
            $table->dropColumn('dpp_amount_other');
        });
    }
};
