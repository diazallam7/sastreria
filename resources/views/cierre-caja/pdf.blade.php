<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre de Caja - {{ \Carbon\Carbon::parse($movimientos['fecha'])->format('d/m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
            background: #ffffff;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 2px solid #34495e;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 16px;
            color: #34495e;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header .date {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 3px;
        }

        .header .generated {
            font-size: 10px;
            color: #95a5a6;
        }

        .summary-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .summary-header {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-content {
            padding: 15px;
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .summary-item .label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

        .summary-item .value.positive {
            color: #27ae60;
        }

        .summary-item .value.negative {
            color: #e74c3c;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin: 25px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .breakdown-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .breakdown-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .breakdown-column:first-child {
            margin-right: 4%;
        }

        .breakdown-header {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .breakdown-header.ingresos {
            background: #27ae60;
        }

        .breakdown-header.egresos {
            background: #e74c3c;
        }

        .breakdown-content {
            padding: 15px;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .breakdown-item:last-child {
            border-bottom: none;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #34495e;
            font-weight: bold;
        }

        .detail-section {
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: white;
            page-break-inside: avoid;
        }

        .detail-header {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 11px;
        }

        .detail-content {
            padding: 15px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-info {
            flex: 1;
        }

        .detail-amount {
            font-weight: bold;
            color: #2c3e50;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
            margin-left: 5px;
        }

        .badge.success {
            background: #27ae60;
            color: white;
        }

        .badge.warning {
            background: #f39c12;
            color: white;
        }

        .badge.danger {
            background: #e74c3c;
            color: white;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
        }

        .page-break {
            page-break-before: always;
        }

        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Cierre de Caja</h1>
        <div class="subtitle">Reporte Diario</div>
        <div class="date">{{ \Carbon\Carbon::parse($movimientos['fecha'])->format('d/m/Y') }}</div>
        <div class="generated">Generado el: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Resumen Principal -->
    <div class="summary-section">
        <div class="summary-header">Resumen del Día</div>
        <div class="summary-content">
            <div class="summary-item">
                <div class="label">Total Ingresos</div>
                <div class="value positive">Gs {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Egresos</div>
                <div class="value negative">Gs {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Saldo Neto</div>
                <div class="value {{ $movimientos['saldo_neto'] >= 0 ? 'positive' : 'negative' }}">
                    Gs {{ number_format($movimientos['saldo_neto'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Desglose de Ingresos y Egresos -->
    <div class="breakdown-section">
        <div class="breakdown-column">
            <div class="breakdown-header ingresos">Ingresos del Día</div>
            <div class="breakdown-content">
                <div class="breakdown-item">
                    <span>Alquileres Iniciados</span>
                    <span>Gs {{ number_format($movimientos['ingresos']['alquileres'], 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-item">
                    <span>Multas por Retraso</span>
                    <span>Gs {{ number_format($movimientos['ingresos']['multas_retraso'], 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-item">
                    <span>Ventas</span>
                    <span>Gs {{ number_format($movimientos['ingresos']['ventas'], 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-item">
                    <span>Ingresos por Cancelaciones</span>
                    <span>Gs {{ number_format($movimientos['ingresos']['ingresos_cancelaciones'], 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-item">
                    <span>TOTAL INGRESOS</span>
                    <span>Gs {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="breakdown-column">
            <div class="breakdown-header egresos">Egresos del Día</div>
            <div class="breakdown-content">
                <div class="breakdown-item">
                    <span>Compras</span>
                    <span>Gs {{ number_format($movimientos['egresos']['compras'], 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-item">
                    <span>Gastos Varios</span>
                    <span>Gs {{ number_format($movimientos['egresos']['gastos_varios'], 0, ',', '.') }}</span>
                </div>
                <div class="breakdown-item">
                    <span>TOTAL EGRESOS</span>
                    <span>Gs {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Compras -->
    @if(count($movimientos['detalles']['compras']) > 0)
    <div class="detail-section">
        <div class="detail-header">
            Compras del Día ({{ count($movimientos['detalles']['compras']) }})
        </div>
        <div class="detail-content">
            @foreach($movimientos['detalles']['compras'] as $compra)
            <div class="detail-item">
                <div class="detail-info">
                    <strong>{{ $compra->nombre_producto }}</strong><br>
                    <small>
                        {{ $compra->cantidad_total_calculada ?? $compra->cantidad_total }} unidades 
                        - {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}
                        @if(isset($compra->precio_total_calculado))
                            <br>Precio unitario: Gs{{ number_format($compra->precio_compra, 0, ',', '.') }}
                        @endif
                    </small>
                </div>
                <div class="detail-amount">Gs {{ number_format($compra->precio_total_calculado ?? $compra->precio_compra, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detalle de Alquileres Iniciados -->
    @if(count($movimientos['detalles']['alquileres_iniciados']) > 0)
    <div class="detail-section">
        <div class="detail-header">
            Alquileres Iniciados ({{ count($movimientos['detalles']['alquileres_iniciados']) }})
        </div>
        <div class="detail-content">
            @foreach($movimientos['detalles']['alquileres_iniciados'] as $alquiler)
            <div class="detail-item">
                <div class="detail-info">
                    <strong>{{ $alquiler->cliente->nombre ?? 'Cliente' }}</strong><br>
                    <small>Alquiler #{{ $alquiler->id }} - {{ \Carbon\Carbon::parse($alquiler->fecha_inicio)->format('d/m/Y') }}</small>
                </div>
                <div class="detail-amount">Gs {{ number_format($alquiler->costo_total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detalle de Devoluciones (Multas) -->
    @if(count($movimientos['detalles']['devoluciones']) > 0)
    <div class="detail-section">
        <div class="detail-header">
            Devoluciones con Multas ({{ count($movimientos['detalles']['devoluciones']) }})
        </div>
        <div class="detail-content">
            @foreach($movimientos['detalles']['devoluciones'] as $devolucion)
            <div class="detail-item">
                <div class="detail-info">
                    <strong>{{ $devolucion->alquiler->cliente->nombre ?? 'Cliente' }}</strong><br>
                    <small>Devolución #{{ $devolucion->id }}</small>
                    @if(isset($devolucion->multa) && $devolucion->multa > 0)
                        <span class="badge warning">Multa Aplicada</span>
                    @endif
                </div>
                <div class="detail-amount">Gs {{ number_format($devolucion->multa_aplicada_real ?? $devolucion->multa_aplicada ?? 0, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detalle de Ventas -->
    @if(count($movimientos['detalles']['ventas']) > 0)
    <div class="detail-section">
        <div class="detail-header">
            Ventas del Día ({{ count($movimientos['detalles']['ventas']) }})
        </div>
        <div class="detail-content">
            @foreach($movimientos['detalles']['ventas'] as $venta)
            <div class="detail-item">
                <div class="detail-info">
                    <strong>{{ $venta->cliente->nombre ?? 'Cliente' }}</strong><br>
                    <small>Venta #{{ $venta->id }} - {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</small>
                </div>
                <div class="detail-amount">Gs {{ number_format($venta->precio_total, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detalle de Cancelaciones -->
    @if(count($movimientos['detalles']['cancelaciones']) > 0)
    <div class="detail-section">
        <div class="detail-header">
            Cancelaciones del Día ({{ count($movimientos['detalles']['cancelaciones']) }})
        </div>
        <div class="detail-content">
            @foreach($movimientos['detalles']['cancelaciones'] as $cancelacion)
            @php
                $totalRecibido = $cancelacion->seña_alquiler + $cancelacion->seña_garantia;
                $devuelto = $cancelacion->seña_devuelta ?? 0;
                $ingresoNeto = $totalRecibido - $devuelto;
            @endphp
            <div class="detail-item">
                <div class="detail-info">
                    <strong>{{ $cancelacion->cliente->nombre ?? 'Cliente' }}</strong><br>
                    <small>Reserva #{{ $cancelacion->id }}</small>
                    <span class="badge danger">Cancelada</span><br>
                    <small>Recibido: Gs{{ number_format($totalRecibido, 0, ',', '.') }} | Devuelto: Gs{{ number_format($devuelto, 0, ',', '.') }}</small>
                </div>
                <div class="detail-amount">Gs {{ number_format($ingresoNeto, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Detalle de Gastos Varios -->
    @if(count($movimientos['detalles']['gastos']) > 0)
    <div class="detail-section">
        <div class="detail-header">
            Gastos Varios del Día ({{ count($movimientos['detalles']['gastos']) }})
        </div>
        <div class="detail-content">
            @foreach($movimientos['detalles']['gastos'] as $gasto)
            <div class="detail-item">
                <div class="detail-info">
                    <strong>{{ $gasto->nombre_gasto }}</strong><br>
                    @if($gasto->observacion)
                        <small>{{ $gasto->observacion }}</small>
                    @endif
                </div>
                <div class="detail-amount">Gs {{ number_format($gasto->monto, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Documento generado automáticamente por el Sistema de Gestión</strong></p>
        <p>© {{ date('Y') }} - Todos los derechos reservados | Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
