<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItemAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_item_id',
        'tindakan',
        'qty',
        'satuan',
        'harga_satuan',
        'jumlah_harga',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'decimal:2',
        'jumlah_harga' => 'decimal:2',
    ];

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($action) {
            $action->jumlah_harga = $action->qty * $action->harga_satuan;
        });
    }
} 