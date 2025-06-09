<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Exports\InvoiceExport;
use Filament\Actions\CreateAction;
use Filament\Actions\Exports\ExportsAction;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Faktur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Faktur')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Nomor Faktur'),
                        Forms\Components\Select::make('tax_type')
                            ->required()
                            ->options([
                                'tax' => 'Pajak',
                                'non_tax' => 'Non Pajak',
                            ])
                            ->label('Tipe Faktur')
                            ->default('tax')
                            ->live(),
                        Forms\Components\Select::make('ppn_rate')
                            ->required()
                            ->options([
                                '11.00' => '11%',
                                '12.00' => '12%',
                            ])
                            ->default('11.00')
                            ->label('PPN')
                            ->visible(fn (Forms\Get $get) => $get('tax_type') === 'tax'),
                    ]),

                Forms\Components\Section::make('Barang')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('nama_barang')->required()->label('Nama Barang'),
                                Forms\Components\TextInput::make('nomor_inventaris')->label('Nomor Inventaris'),
                                Forms\Components\TextInput::make('bagian')->required()->label('Bagian'),
                                Forms\Components\Repeater::make('actions')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('tindakan')->required()->label('Tindakan'),
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('qty')->required()->numeric()->default(1)->label('Qty'),
                                            Forms\Components\Select::make('satuan')->required()->options(['Unit'=>'Unit','Keping'=>'Keping'])->default('Unit')->label('Satuan'),
                                            Forms\Components\TextInput::make('harga_satuan')->required()->numeric()->prefix('Rp')->label('Harga Satuan'),
                                        ]),
                                        Forms\Components\TextInput::make('jumlah_harga')->disabled()->prefix('Rp')->label('Jumlah Harga'),
                                    ])
                                    ->columns(1)
                                    ->defaultItems(1)
                                    ->reorderable(false)
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Ringkasan')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->disabled()
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->label('Sub Total'),
                        Forms\Components\TextInput::make('ppn_amount')
                            ->disabled()
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->label('PPN')
                            ->visible(fn (Forms\Get $get) => $get('tax_type') === 'tax'),
                        Forms\Components\TextInput::make('grand_total')
                            ->disabled()
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->label('Grand Total'),
                    ]),

                Forms\Components\Section::make('Keterangan Tambahan')
                    ->schema([
                        Forms\Components\TextInput::make('tempat')
                            ->label('Tempat (Lokasi)'),
                        Forms\Components\DatePicker::make('tanggal_surat')
                            ->label('Tanggal Surat'),
                        Forms\Components\Textarea::make('kepada')
                            ->label('Kepada Yth.'),
                        Forms\Components\TextInput::make('di_lokasi')
                            ->label('Di (Lokasi)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor Faktur'),
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->label('Nama Barang'),
                Tables\Columns\TextColumn::make('tax_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tax' => 'success',
                        'non_tax' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tax' => 'Pajak',
                        'non_tax' => 'Non Pajak',
                    })
                    ->label('Tipe Faktur'),
                Tables\Columns\TextColumn::make('subtotal_calculated')
                    ->money('IDR')
                    ->sortable()
                    ->label('Sub Total'),
                Tables\Columns\TextColumn::make('ppn_amount_calculated')
                    ->money('IDR')
                    ->sortable()
                    ->label('PPN'),
                Tables\Columns\TextColumn::make('grand_total_calculated')
                    ->money('IDR')
                    ->sortable()
                    ->label('Grand Total'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Tanggal Dibuat'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
                Tables\Actions\Action::make('export_excel')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        return redirect()->route('invoices.export', $record->id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportsAction::make()
                ->exporter(InvoiceExport::class)
                ->label('Export Excel'),
        ];
    }
}
