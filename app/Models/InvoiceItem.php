<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'nama_barang',
        'nomor_inventaris',
        'bagian',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(InvoiceItemAction::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->actions->sum('jumlah_harga');
    }
}
