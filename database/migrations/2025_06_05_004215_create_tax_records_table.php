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
        Schema::create('tax_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('customer_name');
            $table->string('project_name');
            $table->text('description');
            $table->decimal('pph_rate', 4, 2); // 1.50 atau 2.00
            $table->decimal('ppn_rate', 4, 2); // 11.00 atau 12.00
            $table->string('unit_type'); // PCS atau Unit
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('total_price', 15, 2); // quantity * price
            $table->decimal('ppn_amount', 15, 2); // total_price * ppn_rate
            $table->decimal('pph_amount', 15, 2); // total_price * pph_rate
            $table->decimal('dpp_amount', 15, 2); // total_price * (11/12 atau 12/12)
            $table->decimal('grand_total', 15, 2); // total_price + pph_amount
            $table->decimal('sp2d_value', 15, 2); // grand_total - pph_amount
            $table->string('invoice_type'); // 020 atau 040
            $table->string('invoice_number');
            $table->string('no_kw')->nullable();
            $table->date('tanggal_kw')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_records');
    }
};
