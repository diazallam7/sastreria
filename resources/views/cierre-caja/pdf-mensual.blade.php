<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Mensual de Caja</title>
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

        .week-detail {
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: white;
            page-break-inside: avoid;
        }

        .week-header {
            background: #34495e;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 11px;
        }

        .week-content {
            padding: 15px;
            display: table;
            width: 100%;
        }

        .week-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .week-column:first-child {
            margin-right: 4%;
        }

        .week-summary {
            margin-top: 15px;
            padding: 10px;
            background: #ecf0f1;
            text-align: center;
            border-radius: 3px;
        }

        .week-summary .amount {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
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
        <h1>Resumen Mensual de Caja</h1>
        <div class="subtitle">{{ $nombreMes }} {{ $año }}</div>
        <div class="period">Período: {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</div>
        <div class="generated">Generado el: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Resumen Principal -->
    <div class="summary-section">
        <div class="summary-header">Resumen Ejecutivo</div>
        <div class="summary-content">
            <div class="summary-item">
                <div class="label">Total Ingresos</div>
                <div class="value positive">Gs {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Egresos</div>
                <div class="value negative">Gs {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Saldo Neto</div>
                <div class="value {{ $totalesMes['saldo_neto'] >= 0 ? 'positive' : 'negative' }}">
                    Gs {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Resumen Semanal -->
    <div class="section-title">Análisis Semanal</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Semana</th>
                <th style="width: 20%;">Período</th>
                <th style="width: 20%;" class="text-right">Ingresos</th>
                <th style="width: 20%;" class="text-right">Egresos</th>
                <th style="width: 25%;" class="text-right">Saldo Neto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientosMensuales as $semana)
            <tr>
                <td><strong>Semana {{ $semana['semana'] }}</strong></td>
                <td>{{ $semana['fecha_inicio'] }} al {{ $semana['fecha_fin'] }}</td>
                <td class="text-right">Gs {{ number_format($semana['ingresos'], 0, ',', '.') }}</td>
                <td class="text-right">Gs {{ number_format($semana['egresos'], 0, ',', '.') }}</td>
                <td class="text-right">
                    <strong>Gs {{ number_format($semana['saldo_neto'], 0, ',', '.') }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="no-data">No hay datos disponibles para este período</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">TOTALES</th>
                <th class="text-right">Gs {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</th>
                <th class="text-right">Gs {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</th>
                <th class="text-right">Gs {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Estadísticas -->
    <div class="section-title">Indicadores de Rendimiento</div>
    <div class="statistics-grid">
        <div class="stat-item">
            <div class="stat-label">Mejor Semana</div>
            <div class="stat-value">Semana {{ $mejorSemana['numero'] ?? 'N/A' }}</div>
            <div class="stat-amount">Gs {{ number_format($mejorSemana['saldo'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Peor Semana</div>
            <div class="stat-value">Semana {{ $peorSemana['numero'] ?? 'N/A' }}</div>
            <div class="stat-amount">Gs {{ number_format($peorSemana['saldo'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="statistics-grid">
        <div class="stat-item">
            <div class="stat-label">Promedio Semanal - Ingresos</div>
            <div class="stat-amount">Gs {{ number_format($promedios['ingresos'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Promedio Semanal - Egresos</div>
            <div class="stat-amount">Gs {{ number_format($promedios['egresos'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Desglose por Categorías -->
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
                        
                        foreach ($movimientosMensuales as $semana) {
                            foreach ($categorias as $key => $value) {
                                $categorias[$key]['total'] += $semana['desglose']['ingresos'][$key] ?? 0;
                            }
                        }
                    @endphp
                    
                    @foreach($categorias as $key => $categoria)
                    @php
                        $porcentaje = $totalesMes['ingresos'] > 0 ? ($categoria['total'] / $totalesMes['ingresos']) * 100 : 0;
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
                        <th class="text-right">Gs {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</th>
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
                        
                        foreach ($movimientosMensuales as $semana) {
                            foreach ($categoriasEgresos as $key => $value) {
                                $categoriasEgresos[$key]['total'] += $semana['desglose']['egresos'][$key] ?? 0;
                            }
                        }
                    @endphp
                    
                    @foreach($categoriasEgresos as $key => $categoria)
                    @php
                        $porcentaje = $totalesMes['egresos'] > 0 ? ($categoria['total'] / $totalesMes['egresos']) * 100 : 0;
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
                        <th class="text-right">Gs {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</th>
                        <th class="text-right">100.0%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Desglose Semanal Detallado -->
    <div class="page-break"></div>
    <div class="section-title">Desglose Semanal Detallado</div>
    
    @foreach($movimientosMensuales as $semana)
    <div class="week-detail">
        <div class="week-header">
            Semana {{ $semana['semana'] }} - {{ $semana['fecha_inicio'] }} al {{ $semana['fecha_fin'] }}
        </div>
        <div class="week-content">
            <div class="week-column">
                <div class="category-title">Ingresos</div>
                <table>
                    <tbody>
                        <tr>
                            <td>Alquileres Iniciados</td>
                            <td class="text-right">Gs {{ number_format($semana['desglose']['ingresos']['alquileres'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Multas por Retraso</td>
                            <td class="text-right">Gs {{ number_format($semana['desglose']['ingresos']['multas_retraso'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ventas</td>
                            <td class="text-right">Gs {{ number_format($semana['desglose']['ingresos']['ventas'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ingresos por Cancelaciones</td>
                            <td class="text-right">Gs {{ number_format($semana['desglose']['ingresos']['ingresos_cancelaciones'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Ingresos</th>
                            <th class="text-right">Gs {{ number_format($semana['ingresos'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="week-column">
                <div class="category-title">Egresos</div>
                <table>
                    <tbody>
                        <tr>
                            <td>Compras</td>
                            <td class="text-right">Gs {{ number_format($semana['desglose']['egresos']['compras'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Gastos Varios</td>
                            <td class="text-right">Gs {{ number_format($semana['desglose']['egresos']['gastos_varios'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Egresos</th>
                            <th class="text-right">Gs {{ number_format($semana['egresos'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="week-summary">
            <strong>Saldo Neto de la Semana: 
                <span class="amount">Gs {{ number_format($semana['saldo_neto'], 0, ',', '.') }}</span>
            </strong>
        </div>
    </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p><strong>Documento generado automáticamente por el Sistema de Gestión</strong></p>
        <p>© {{ date('Y') }} - Todos los derechos reservados | Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
