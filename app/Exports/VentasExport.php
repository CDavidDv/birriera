<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ventas;

    public function __construct($ventas)
    {
        $this->ventas = $ventas;
    }

    public function collection()
    {
        return collect($this->ventas);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Hora',
            'Mesa',
            'Cliente',
            'Método de Pago',
            'Subtotal',
            'Descuento',
            'Propina',
            'Total',
            'Estado'
        ];
    }

    public function map($venta): array
    {
        return [
            $venta->id,
            $venta->created_at->format('Y-m-d'),
            $venta->created_at->format('H:i:s'),
            $venta->mesa ? $venta->mesa->nombre : 'Para llevar',
            $venta->nombre_cliente ?? 'Sin nombre',
            $this->getMetodoPago($venta->metodo_pago),
            $venta->subtotal ?? 0,
            $venta->descuento ?? 0,
            $venta->propina ?? 0,
            $venta->total,
            $venta->estado
        ];
    }

    protected function getMetodoPago($metodo)
    {
        $metodos = [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia'
        ];

        return $metodos[$metodo] ?? $metodo;
    }
} 