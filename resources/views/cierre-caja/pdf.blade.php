<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre de Caja - {{ \Carbon\Carbon::parse($movimientos['fecha'])->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            background-color: #f5f5f5;
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        table th {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CIERRE DE CAJA</h1>
        <p>Fecha: {{ \Carbon\Carbon::parse($movimientos['fecha'])->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <div class="summary-title">RESUMEN GENERAL</div>
        <div class="summary-row">
            <span>Total Ingresos:</span>
            <span>₲ {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Total Egresos:</span>
            <span>₲ {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row total-row">
            <span>Saldo Neto:</span>
            <span class="{{ $movimientos['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                ₲ {{ number_format($movimientos['saldo_neto'], 0, ',', '.') }}
            </span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DESGLOSE DE INGRESOS</div>
        <table>
            <tr>
                <th>Concepto</th>
                <th class="text-right">Monto</th>
            </tr>
            <tr>
                <td>Señas de Reservas</td>
                <td class="text-right">₲ {{ number_format($movimientos['ingresos']['señas_recibidas'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Alquileres</td>
                <td class="text-right">₲ {{ number_format($movimientos['ingresos']['alquileres'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Multas por Retraso</td>
                <td class="text-right">₲ {{ number_format($movimientos['ingresos']['multas_retraso'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ventas</td>
                <td class="text-right">₲ {{ number_format($movimientos['ingresos']['ventas'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ingresos por Cancelaciones</td>
                <td class="text-right">₲ {{ number_format($movimientos['ingresos']['ingresos_cancelaciones'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>TOTAL INGRESOS</th>
                <th class="text-right">₲ {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</th>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">DESGLOSE DE EGRESOS</div>
        <table>
            <tr>
                <th>Concepto</th>
                <th class="text-right">Monto</th>
            </tr>
            <tr>
                <td>Devoluciones por Cancelaciones</td>
                <td class="text-right">₲ {{ number_format($movimientos['egresos']['devoluciones_cancelaciones'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Garantías Devueltas</td>
                <td class="text-right">₲ {{ number_format($movimientos['egresos']['garantias_devueltas'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Compras</td>
                <td class="text-right">₲ {{ number_format($movimientos['egresos']['compras'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Gastos Varios</td>
                <td class="text-right">₲ {{ number_format($movimientos['egresos']['gastos_varios'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>TOTAL EGRESOS</th>
                <th class="text-right">₲ {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</th>
            </tr>
        </table>
    </div>

    <!-- Detalles de Movimientos -->
    @if(count($movimientos['detalles']['reservas']) > 0)
    <div class="section">
        <div class="section-title">RESERVAS DEL DÍA</div>
        <table>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Monto</th>
            </tr>
            @foreach($movimientos['detalles']['reservas'] as $reserva)
            <tr>
                <td>{{ $reserva->id }}</td>
                <td>{{ $reserva->cliente->nombre }}</td>
                <td>{{ ucfirst($reserva->estado) }}</td>
                <td class="text-right">₲ {{ number_format($reserva->seña_alquiler + $reserva->seña_garantia, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    @if(count($movimientos['detalles']['cancelaciones']) > 0)
    <div class="section">
        <div class="section-title">CANCELACIONES DEL DÍA</div>
        <table>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th class="text-right">Recibido</th>
                <th class="text-right">Devuelto</th>
                <th class="text-right">Neto</th>
            </tr>
            @foreach($movimientos['detalles']['cancelaciones'] as $cancelacion)
            @php
                $totalRecibido = $cancelacion->seña_alquiler + $cancelacion->seña_garantia;
                $devuelto = $cancelacion->seña_devuelta ?? 0;
                $ingresoNeto = $totalRecibido - $devuelto;
            @endphp
            <tr>
                <td>{{ $cancelacion->id }}</td>
                <td>{{ $cancelacion->cliente->nombre }}</td>
                <td class="text-right">₲ {{ number_format($totalRecibido, 0, ',', '.') }}</td>
                <td class="text-right">₲ {{ number_format($devuelto, 0, ',', '.') }}</td>
                <td class="text-right">₲ {{ number_format($ingresoNeto, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>