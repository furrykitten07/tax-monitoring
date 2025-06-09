<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('item_name');
            $table->string('inventory_number')->nullable();
            $table->string('department');
            $table->enum('tax_type', ['tax', 'non_tax'])->default('tax');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('ppn_rate', 5, 2)->default(11.00);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('tempat')->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->string('kepada')->nullable();
            $table->string('di_lokasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
}; 