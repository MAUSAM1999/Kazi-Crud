<?php

namespace YajTech\Crud\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class CommonExport implements FromCollection, WithMapping, WithHeadings, WithEvents, ShouldAutoSize
{
    protected $model;

    protected $mapping;

    protected $headers;

    public function __construct($model, $headers, $mapping)
    {
        $this->model = $model;
        $this->headers = $headers;
        $this->mapping = $mapping;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function collection()
    {
        $model = $this->model->map(function ($item, $index) {
            $item->s_no = $index + 1;

            return $item;
        });

        return $model;
    }

    public function map($model): array
    {
        return call_user_func($this->mapping, $model);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:W1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(16);
                $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(30);
                $lastrow = $event->sheet->getDelegate()->getHighestRow();
                $event->sheet->getDelegate()->getStyle('A1:Z' . $lastrow)->getAlignment()->setWrapText(true);
            },

        ];
    }
}
