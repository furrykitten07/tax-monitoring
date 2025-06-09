<?php

namespace App\Filament\Widgets;

use App\Models\TaxRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaxSummary extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $instansiRecords = TaxRecord::where('invoice_type', '020')->get();
        $swastaRecords = TaxRecord::where('invoice_type', '040')->get();
        $allRecords = TaxRecord::all();

        $instansiTotal = $instansiRecords->sum('total_price');
        $instansiPPN = $instansiRecords->sum('ppn_amount');
        $instansiPPH = $instansiRecords->sum('pph_amount');
        $instansiGrandTotal = $instansiRecords->sum('grand_total');
        $instansiDPP = $instansiRecords->sum('dpp_amount');

        $swastaTotal = $swastaRecords->sum('total_price');
        $swastaPPN = $swastaRecords->sum('ppn_amount');
        $swastaPPH = $swastaRecords->sum('pph_amount');
        $swastaGrandTotal = $swastaRecords->sum('grand_total');
        $swastaDPP = $swastaRecords->sum('dpp_amount');

        $allTotal = $allRecords->sum('total_price');
        $allPPN = $allRecords->sum('ppn_amount');
        $allPPH = $allRecords->sum('pph_amount');
        $allGrandTotal = $allRecords->sum('grand_total');
        $allDPP = $allRecords->sum('dpp_amount');

        return [
            Stat::make('TOTAL TRANSAKSI KESELURUHAN', '')
                ->description('Total Faktur: ' . $allRecords->count())
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color('primary'),
            Stat::make('Jumlah Harga Keseluruhan', 'Rp ' . number_format($allTotal, 0, ',', '.'))
                ->description('DPP Lain Lain: Rp ' . number_format($allDPP, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
            Stat::make('Total PPN & PPH', 'Rp ' . number_format($allPPN + $allPPH, 0, ',', '.'))
                ->description('PPN: Rp ' . number_format($allPPN, 0, ',', '.') . ' | PPH: Rp ' . number_format($allPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('TOTAL', 'Rp ' . number_format($allGrandTotal, 0, ',', '.'))
                ->description('Nilai SP2D: Rp ' . number_format($allGrandTotal - $allPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            Stat::make('Ringkasan Transaksi Instansi', '')
                ->description('Total Faktur: ' . $instansiRecords->count())
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
            Stat::make('Jumlah Harga Instansi', 'Rp ' . number_format($instansiTotal, 0, ',', '.'))
                ->description('Total PPN: Rp ' . number_format($instansiPPN, 0, ',', '.') . ' | Total PPH: Rp ' . number_format($instansiPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('Total Setelah Pajak Instansi', 'Rp ' . number_format($instansiGrandTotal, 0, ',', '.'))
                ->description('Nilai SP2D: Rp ' . number_format($instansiGrandTotal - $instansiPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Ringkasan Transaksi Swasta', '')
                ->description('Total Faktur: ' . $swastaRecords->count())
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('warning'),
            Stat::make('Jumlah Harga Swasta', 'Rp ' . number_format($swastaTotal, 0, ',', '.'))
                ->description('Total PPN: Rp ' . number_format($swastaPPN, 0, ',', '.') . ' | Total PPH: Rp ' . number_format($swastaPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
            Stat::make('Total Setelah Pajak Swasta', 'Rp ' . number_format($swastaGrandTotal, 0, ',', '.'))
                ->description('Nilai SP2D: Rp ' . number_format($swastaGrandTotal - $swastaPPH, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
