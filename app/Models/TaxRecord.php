<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'customer_name',
        'project_name',
        'description',
        'pph_rate',
        'ppn_rate',
        'unit_type',
        'quantity',
        'price',
        'total_price',
        'ppn_amount',
        'pph_amount',
        'dpp_amount',
        'dpp_amount_other',
        'grand_total',
        'sp2d_value',
        'invoice_type',
        'invoice_number',
        'no_kw',
        'tanggal_kw',
        'tanggal_masuk',
    ];

    protected $casts = [
        'date' => 'date',
        'pph_rate' => 'decimal:2',
        'ppn_rate' => 'decimal:2',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'pph_amount' => 'decimal:2',
        'dpp_amount' => 'decimal:2',
        'dpp_amount_other' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'sp2d_value' => 'decimal:2',
        'tanggal_kw' => 'date',
        'tanggal_masuk' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Total price = quantity x price
            $model->total_price = $model->quantity * $model->price;

            // DPP Asli = Total price (karena harga sudah termasuk PPN)
            $model->dpp_amount = $model->total_price;

            // DPP Lain-lain = Jumlah harga x (11/12)
            $model->dpp_amount_other = $model->total_price * (11/12);

            // PPN = Jumlah harga x ppn_rate
            $model->ppn_amount = $model->total_price * ($model->ppn_rate / 100);

            // PPH = Jumlah harga x pph_rate
            $model->pph_amount = $model->total_price * ($model->pph_rate / 100);

            // TOTAL = Jumlah harga + PPN
            $model->grand_total = $model->total_price + $model->ppn_amount;

            // NILAI SP2D = Total - PPH
            $model->sp2d_value = $model->grand_total - $model->pph_amount;
        });
    }
}
