<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxRecordResource\Pages;
use App\Filament\Resources\TaxRecordResource\RelationManagers;
use App\Models\TaxRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Exports\TaxRecordExporter;
use Filament\Notifications\Notification;

class TaxRecordResource extends Resource
{
    protected static ?string $model = TaxRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Data Pajak';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->label('Tanggal'),
                Forms\Components\TextInput::make('customer_name')
                    ->required()
                    ->label('Nama Customer'),
                Forms\Components\TextInput::make('project_name')
                    ->required()
                    ->label('Nama Project'),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->label('Uraian'),
                Forms\Components\Select::make('pph_rate')
                    ->required()
                    ->options([
                        '1.50' => '1.5%',
                        '2.00' => '2%',
                    ])
                    ->label('PPH'),
                Forms\Components\Select::make('ppn_rate')
                    ->required()
                    ->options([
                        '11.00' => '11%',
                        '12.00' => '12%',
                    ])
                    ->label('PPN'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('unit_type')
                            ->required()
                            ->options([
                                'PCS' => 'PCS',
                                'Unit' => 'Unit',
                            ])
                            ->label('Satuan'),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->label('Jumlah Barang'),
                    ]),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Harga Satuan'),
                Forms\Components\TextInput::make('total_price')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('Jumlah Harga'),
                Forms\Components\TextInput::make('dpp_amount')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('DPP Asli (Total Harga)'),
                Forms\Components\TextInput::make('dpp_amount_other')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('DPP Lain-lain (DPP Bersih)'),
                Forms\Components\TextInput::make('ppn_amount')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('Jumlah PPN'),
                Forms\Components\TextInput::make('pph_amount')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('Jumlah PPH'),
                Forms\Components\TextInput::make('grand_total')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('TOTAL (Jumlah Harga + PPN)'),
                Forms\Components\TextInput::make('sp2d_value')
                    ->disabled()
                    ->prefix('Rp')
                    ->label('Nilai SP2D (TOTAL - PPH)'),
                Forms\Components\Select::make('invoice_type')
                    ->required()
                    ->options([
                        '020' => '020 (Instansi)',
                        '040' => '040 (Swasta)',
                    ])
                    ->label('Tipe Faktur'),
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->label('Nomor Faktur'),
                Forms\Components\TextInput::make('no_kw')
                    ->label('No KW'),
                Forms\Components\DatePicker::make('tanggal_kw')
                    ->label('Tanggal KW'),
                Forms\Components\DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->label('Nama Customer'),
                Tables\Columns\TextColumn::make('project_name')
                    ->searchable()
                    ->label('Nama Project'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->wrap()
                    ->limit(50)
                    ->tooltip(function ($record): ?string {
                        return $record->description;
                    })
                    ->label('Uraian'),
                Tables\Columns\TextColumn::make('quantity')
                    ->formatStateUsing(fn ($record) => "{$record->quantity} {$record->unit_type}")
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->label('Harga Satuan'),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->label('Jumlah Harga'),
                Tables\Columns\TextColumn::make('dpp_amount')
                    ->money('IDR')
                    ->label('DPP Asli'),
                Tables\Columns\TextColumn::make('ppn_rate')
                    ->formatStateUsing(fn (string $state): string => "{$state}%")
                    ->label('PPN'),
                Tables\Columns\TextColumn::make('ppn_amount')
                    ->money('IDR')
                    ->label('Jumlah PPN'),
                Tables\Columns\TextColumn::make('pph_rate')
                    ->formatStateUsing(fn (string $state): string => "{$state}%")
                    ->label('PPH'),
                Tables\Columns\TextColumn::make('pph_amount')
                    ->money('IDR')
                    ->label('Jumlah PPH'),
                Tables\Columns\TextColumn::make('dpp_amount_other')
                    ->money('IDR')
                    ->label('DPP Lain-lain'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('IDR')
                    ->label('TOTAL'),
                Tables\Columns\TextColumn::make('sp2d_value')
                    ->money('IDR')
                    ->label('Nilai SP2D'),
                Tables\Columns\TextColumn::make('invoice_type')
                    ->label('Tipe Faktur'),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor Faktur'),
            ])
            ->filters([
                Filter::make('month')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->label('Bulan'),
                        Forms\Components\Select::make('year')
                            ->options([
                                '2023' => '2023',
                                '2024' => '2024',
                                '2025' => '2025',
                            ])
                            ->label('Tahun'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn (Builder $query, $month): Builder => $query->whereMonth('date', $month),
                            )
                            ->when(
                                $data['year'],
                                fn (Builder $query, $year): Builder => $query->whereYear('date', $year),
                            );
                    }),
                SelectFilter::make('invoice_type')
                    ->options([
                        '020' => 'Instansi (020)',
                        '040' => 'Swasta (040)',
                    ])
                    ->label('Tipe Faktur'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->label('Bulan (opsional)')
                            ->placeholder('Pilih bulan'),
                        Forms\Components\Select::make('year')
                            ->options([
                                '2023' => '2023',
                                '2024' => '2024',
                                '2025' => '2025',
                            ])
                            ->default(date('Y'))
                            ->required()
                            ->label('Tahun'),
                        Forms\Components\Select::make('invoice_type')
                            ->options([
                                '020' => 'Instansi (020)',
                                '040' => 'Swasta (040)',
                            ])
                            ->label('Tipe Faktur (opsional)')
                            ->placeholder('Semua tipe faktur'),
                    ])
                    ->action(function (array $data) {
                        $url = route('tax-records.export', array_filter($data));
                        return redirect($url);
                    })
                    ->modalHeading('Filter Export Excel')
                    ->modalDescription('Pilih filter untuk export data pajak ke Excel')
                    ->modalSubmitActionLabel('Export')
                    ->modalCancelActionLabel('Batal'),
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
            'index' => Pages\ListTaxRecords::route('/'),
            'view' => Pages\ViewTaxRecord::route('/{record}'),
            'edit' => Pages\EditTaxRecord::route('/{record}/edit'),
        ];
    }
}
