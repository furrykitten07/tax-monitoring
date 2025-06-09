<?php

namespace App\Filament\Resources\TaxRecordResource\Pages;

use App\Filament\Resources\TaxRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxRecord extends EditRecord
{
    protected static string $resource = TaxRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 