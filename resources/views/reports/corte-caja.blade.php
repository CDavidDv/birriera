<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Corte de Caja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Corte de Caja</h1>
        <p>Fecha: {{ $fecha }}</p>
        <p>Sucursal: {{ $sucursal }}</p>
    </div>

    <div class="section">
        <h2>Resumen Financiero</h2>
        <table>
            <tr>
                <td>Saldo Inicial:</td>
                <td>${{ number_format($corte->saldo_inicial, 2) }}</td>
            </tr>
            <tr>
                <td>Ventas en Efectivo:</td>
                <td>${{ number_format($corte->dinero_en_efectivo, 2) }}</td>
            </tr>
            <tr>
                <td>Ventas con Tarjeta:</td>
                <td>${{ number_format($corte->dinero_tarjeta, 2) }}</td>
            </tr>
            <tr>
                <td>Otros Ingresos:</td>
                <td>${{ number_format($corte->otros_ingresos, 2) }}</td>
            </tr>
            <tr>
                <td>Gastos:</td>
                <td>${{ number_format($corte->gastos, 2) }}</td>
            </tr>
            <tr>
                <td>Venta Real:</td>
                <td>${{ number_format($corte->venta_real, 2) }}</td>
            </tr>
            <tr>
                <td>Efectivo Entregado:</td>
                <td>${{ number_format($corte->efectivo_entregado, 2) }}</td>
            </tr>
            <tr>
                <td>Saldo para Siguiente Corte:</td>
                <td>${{ number_format($corte->saldo_siguiente_corte, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Registros de Caja</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha/Hora</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrosCaja as $registro)
                <tr>
                    <td>{{ $registro->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ ucfirst($registro->tipo) }}</td>
                    <td>{{ $registro->descripcion ?? '-' }}</td>
                    <td>${{ number_format($registro->cantidad, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Ventas del Día</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha/Hora</th>
                    <th>Mesa</th>
                    <th>Método de Pago</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $venta)
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ $venta->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $venta->mesa ? $venta->mesa->nombre : 'Para llevar' }}</td>
                    <td>{{ ucfirst($venta->metodo_pago) }}</td>
                    <td>${{ number_format($venta->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado el {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html> 