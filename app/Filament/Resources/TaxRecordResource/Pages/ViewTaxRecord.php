<?php

namespace App\Filament\Resources\TaxRecordResource\Pages;

use App\Filament\Resources\TaxRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxRecord extends ViewRecord
{
    protected static string $resource = TaxRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Ubah'),
        ];
    }
} 