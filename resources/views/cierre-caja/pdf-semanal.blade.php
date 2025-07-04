<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Semanal de Caja</title>
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

        .header .period {
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

        .summary-item .average {
            font-size: 9px;
            color: #7f8c8d;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            border: 1px solid #dee2e6;
        }

        table th {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #bdc3c7;
            border-right: 1px solid #dee2e6;
        }

        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ecf0f1;
            border-right: 1px solid #dee2e6;
            font-size: 10px;
        }

        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        table tbody tr:hover {
            background: #e8f4f8;
        }

        table tfoot th {
            background: #34495e;
            color: white;
            font-weight: bold;
            border-top: 2px solid #2c3e50;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
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

        .statistics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-item {
            display: table-cell;
            width: 50%;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .stat-item:first-child {
            border-right: none;
        }

        .stat-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .stat-amount {
            font-size: 11px;
            color: #34495e;
        }

        .categories-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .category-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .category-column:first-child {
            margin-right: 4%;
        }

        .category-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #ecf0f1;
            border-left: 4px solid #34495e;
            text-transform: uppercase;
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
        <h1>Resumen Semanal de Caja</h1>
        <div class="subtitle">Análisis Semanal</div>
        <div class="period">{{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</div>
        <div class="generated">Generado el: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Resumen Principal -->
    <div class="summary-section">
        <div class="summary-header">Resumen de la Semana</div>
        <div class="summary-content">
            <div class="summary-item">
                <div class="label">Total Ingresos</div>
                <div class="value positive">Gs {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</div>
                <div class="average">Promedio diario: Gs {{ number_format($promedios['ingresos'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Egresos</div>
                <div class="value negative">Gs {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</div>
                <div class="average">Promedio diario: Gs {{ number_format($promedios['egresos'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Saldo Neto</div>
                <div class="value {{ $totalesSemana['saldo_neto'] >= 0 ? 'positive' : 'negative' }}">
                    Gs {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}
                </div>
                <div class="average">Promedio diario: Gs {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Tabla de Resumen Diario -->
    <div class="section-title">Detalle Diario</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Día</th>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 20%;" class="text-right">Ingresos</th>
                <th style="width: 20%;" class="text-right">Egresos</th>
                <th style="width: 30%;" class="text-right">Saldo Neto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resumenSemanal as $dia)
            <tr>
                <td><strong>{{ $dia['dia'] }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td>
                <td class="text-right">Gs {{ number_format($dia['ingresos'], 0, ',', '.') }}</td>
                <td class="text-right">Gs {{ number_format($dia['egresos'], 0, ',', '.') }}</td>
                <td class="text-right">
                    <strong>Gs {{ number_format($dia['saldo_neto'], 0, ',', '.') }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="no-data">No hay datos disponibles</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">TOTALES</th>
                <th class="text-right">Gs {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</th>
                <th class="text-right">Gs {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</th>
                <th class="text-right">Gs {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Estadísticas -->
    <div class="section-title">Indicadores de Rendimiento</div>
    <div class="statistics-grid">
        <div class="stat-item">
            <div class="stat-label">Mejor Día</div>
            <div class="stat-value">{{ $mejorDia['dia'] ?? 'N/A' }}</div>
            <div class="stat-amount">{{ isset($mejorDia['fecha']) ? \Carbon\Carbon::parse($mejorDia['fecha'])->format('d/m/Y') : 'N/A' }}</div>
            <div class="stat-amount">Gs {{ number_format($mejorDia['saldo'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Peor Día</div>
            <div class="stat-value">{{ $peorDia['dia'] ?? 'N/A' }}</div>
            <div class="stat-amount">{{ isset($peorDia['fecha']) ? \Carbon\Carbon::parse($peorDia['fecha'])->format('d/m/Y') : 'N/A' }}</div>
            <div class="stat-amount">Gs {{ number_format($peorDia['saldo'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Análisis por Categorías -->
    <div class="page-break"></div>
    <div class="section-title">Análisis por Categorías</div>
    
    <div class="categories-section">
        <div class="category-column">
            <div class="category-title">Ingresos por Categoría</div>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $categorias = [
                            'alquileres' => ['nombre' => 'Alquileres Iniciados', 'total' => 0],
                            'multas_retraso' => ['nombre' => 'Multas por Retraso', 'total' => 0],
                            'ventas' => ['nombre' => 'Ventas', 'total' => 0],
                            'ingresos_cancelaciones' => ['nombre' => 'Ingresos por Cancelaciones', 'total' => 0]
                        ];
                        
                        foreach ($resumenSemanal as $dia) {
                            foreach ($categorias as $key => $value) {
                                $categorias[$key]['total'] += $dia['desglose_ingresos'][$key] ?? 0;
                            }
                        }
                    @endphp
                    
                    @foreach($categorias as $key => $categoria)
                    @php
                        $porcentaje = $totalesSemana['ingresos'] > 0 ? ($categoria['total'] / $totalesSemana['ingresos']) * 100 : 0;
                    @endphp
                    @if($categoria['total'] > 0)
                    <tr>
                        <td>{{ $categoria['nombre'] }}</td>
                        <td class="text-right">Gs {{ number_format($categoria['total'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($porcentaje, 1) }}%</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-right">Gs {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</th>
                        <th class="text-right">100.0%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="category-column">
            <div class="category-title">Egresos por Categoría</div>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">%</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $categoriasEgresos = [
                            'compras' => ['nombre' => 'Compras', 'total' => 0],
                            'gastos_varios' => ['nombre' => 'Gastos Varios', 'total' => 0]
                        ];
                        
                        foreach ($resumenSemanal as $dia) {
                            foreach ($categoriasEgresos as $key => $value) {
                                $categoriasEgresos[$key]['total'] += $dia['desglose_egresos'][$key] ?? 0;
                            }
                        }
                    @endphp
                    
                    @foreach($categoriasEgresos as $key => $categoria)
                    @php
                        $porcentaje = $totalesSemana['egresos'] > 0 ? ($categoria['total'] / $totalesSemana['egresos']) * 100 : 0;
                    @endphp
                    @if($categoria['total'] > 0)
                    <tr>
                        <td>{{ $categoria['nombre'] }}</td>
                        <td class="text-right">Gs {{ number_format($categoria['total'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($porcentaje, 1) }}%</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-right">Gs {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</th>
                        <th class="text-right">100.0%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Documento generado automáticamente por el Sistema de Gestión</strong></p>
        <p>© {{ date('Y') }} - Todos los derechos reservados | Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
