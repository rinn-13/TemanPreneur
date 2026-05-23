<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceExport implements FromArray, WithHeadings, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $summary = [
            ['Total Pengguna', $this->data['total_users'] ?? 0],
            ['Total Pesanan', $this->data['total_orders'] ?? 0],
            ['Total Pendapatan', 'Rp ' . number_format($this->data['total_revenue'] ?? 0)],
            ['Total Produk', $this->data['total_products'] ?? 0],
        ];

        $dailyData = [];
        if (isset($this->data['orders_per_day']) && is_array($this->data['orders_per_day'])) {
            $dailyData = array_map(function ($item) {
                return [
                    $item['date'] ?? $item->date ?? '',
                    $item['total'] ?? $item->total ?? 0,
                ];
            }, $this->data['orders_per_day']);
        }

        // Combine summary and daily data
        $result = array_merge($summary, [[''], []], [['Tanggal', 'Jumlah Pesanan']], $dailyData);

        return $result;
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9ECEF']]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9ECEF']]],
        ];
    }
}
