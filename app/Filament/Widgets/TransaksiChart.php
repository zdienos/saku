<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Support\RawJs;

class TransaksiChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'transaksiChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Transaksi 7 hari terakhir';

    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 3;
    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $masuk = $keluar = $labels = [];
        foreach (Transaksi::groupByRaw('substr(created_at,1,10)')
            ->orderBy('created_at')
            ->selectRaw('
    created_at as tanggal,
    sum(if(substr(kode,1,1)=\'M\',jumlah,0)) as masuk,
    sum(if(substr(kode,1,1)=\'K\',jumlah,0)) as keluar')
            ->get() as $t) {
            $masuk[] = $t->masuk;
            $keluar[] = $t->keluar;
            $labels[] = date('d/m/Y', strtotime($t->tanggal));
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'colors' => ["#4aca5b", "#f85c44"],
            'series' => [
                [
                    'name' => 'Pemasukan',
                    'data' => $masuk,
                ],
                [
                    'name' => 'Pengeluaran',
                    'data' => $keluar,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
    {
        yaxis: {
            labels: {
                formatter: function (val, index) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val, opt) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
            },
            dropShadow: {
                enabled: true
            },
        }
    }
    JS);
    }
}
