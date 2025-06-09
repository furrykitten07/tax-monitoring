<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class RecentInvoicesTable extends BaseWidget
{
    protected static ?string $heading = '5 Faktur Terbaru';

    public function getTableQuery(): Builder|Relation|null
    {
        return Invoice::query()->latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('invoice_number')->label('Nomor Faktur')->searchable(),
            TextColumn::make('created_at')->label('Tanggal')->dateTime('d-m-Y'),
            TextColumn::make('grand_total_calculated')->label('Grand Total')->money('IDR'),
            TextColumn::make('tax_type')->label('Tipe Faktur')->formatStateUsing(fn($state) => $state === 'tax' ? 'Pajak' : 'Non Pajak'),
        ];
    }
} 