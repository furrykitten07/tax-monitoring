<?php

namespace App\Filament\Resources\TaxRecordResource\Pages;

use App\Filament\Resources\TaxRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxRecords extends ListRecords
{
    protected static string $resource = TaxRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 