<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'item_name',
        'inventory_number',
        'department',
        'tax_type',
        'subtotal',
        'ppn_rate',
        'ppn_amount',
        'grand_total',
        'tempat',
        'tanggal_surat',
        'kepada',
        'di_lokasi',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'ppn_rate' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tanggal_surat' => 'date',
    ];

    protected $attributes = [
        'subtotal' => 0,
        'ppn_rate' => 11.00,
        'ppn_amount' => 0,
        'grand_total' => 0,
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->subtotal = 0;
            $invoice->ppn_amount = 0;
            $invoice->grand_total = 0;
        });

        static::saving(function ($invoice) {
            // Set item_name and department from first item if not set
            if ($invoice->items->isNotEmpty()) {
                if (!$invoice->item_name) {
                    $invoice->item_name = $invoice->items->first()->nama_barang;
                }
                if (!$invoice->department) {
                    $invoice->department = $invoice->items->first()->bagian;
                }
            }

            // Calculate subtotal from items actions
            $invoice->subtotal = $invoice->items->sum(function($item) {
                return $item->actions->sum('jumlah_harga');
            });

            // Calculate PPN and grand total
            if ($invoice->tax_type === 'tax') {
                $invoice->ppn_amount = $invoice->subtotal * ($invoice->ppn_rate / 100);
                $invoice->grand_total = $invoice->subtotal + $invoice->ppn_amount;
            } else {
                $invoice->ppn_amount = 0;
                $invoice->grand_total = $invoice->subtotal;
            }
        });
    }

    public function getSubtotalCalculatedAttribute()
    {
        return $this->items->sum(function($item) {
            return $item->actions->sum('jumlah_harga');
        });
    }

    public function getPpnAmountCalculatedAttribute()
    {
        if ($this->tax_type === 'tax') {
            return $this->subtotal_calculated * ($this->ppn_rate / 100);
        }
        return 0;
    }

    public function getGrandTotalCalculatedAttribute()
    {
        if ($this->tax_type === 'tax') {
            return $this->subtotal_calculated + $this->ppn_amount_calculated;
        }
        return $this->subtotal_calculated;
    }
}
