<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Mensual de Caja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-box {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .summary-content {
            display: flex;
            justify-content: space-between;
        }
        .summary-item {
            flex: 1;
            padding: 5px;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-primary {
            color: #007bff;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Resumen Mensual de Caja</h1>
        <p>{{ $nombreMes }} {{ $año }}</p>
        <p>Período: {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Resumen Principal -->
    <div class="summary-box">
        <div class="summary-title">Resumen General del Mes</div>
        <div class="summary-content">
            <div class="summary-item">
                <p><strong>Total Ingresos:</strong></p>
                <p class="text-success">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Total Egresos:</strong></p>
                <p class="text-danger">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Saldo Neto:</strong></p>
                <p class="{{ $totalesMes['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ₲ {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Tabla de Resumen Semanal -->
    <h3>Detalle Semanal del Mes</h3>
    <table>
        <thead>
            <tr>
                <th>Semana</th>
                <th>Período</th>
                <th class="text-end">Ingresos</th>
                <th class="text-end">Egresos</th>
                <th class="text-end">Saldo Neto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientosMensuales as $semana)
            <tr>
                <td><strong>Semana {{ $semana['semana'] }}</strong></td>
                <td>{{ $semana['fecha_inicio'] }} al {{ $semana['fecha_fin'] }}</td>
                <td class="text-end">₲ {{ number_format($semana['ingresos'], 0, ',', '.') }}</td>
                <td class="text-end">₲ {{ number_format($semana['egresos'], 0, ',', '.') }}</td>
                <td class="text-end {{ $semana['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ₲ {{ number_format($semana['saldo_neto'], 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">TOTALES</th>
                <th class="text-end">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</th>
                <th class="text-end">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</th>
                <th class="text-end">₲ {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Estadísticas Adicionales -->
    <div class="summary-box">
        <div class="summary-title">Estadísticas del Mes</div>
        <div class="summary-content">
            <div class="summary-item">
                <p><strong>Mejor semana:</strong></p>
                <p>Semana {{ $mejorSemana['numero'] }}</p>
                <p class="text-success">₲ {{ number_format($mejorSemana['saldo'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Peor semana:</strong></p>
                <p>Semana {{ $peorSemana['numero'] }}</p>
                <p class="text-danger">₲ {{ number_format($peorSemana['saldo'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Promedios semanales:</strong></p>
                <p>Ingresos: ₲ {{ number_format($promedios['ingresos'], 0, ',', '.') }}</p>
                <p>Egresos: ₲ {{ number_format($promedios['egresos'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Desglose por Categorías -->
    <div class="page-break"></div>
    <h3>Desglose por Categorías</h3>
    
    <div style="display: flex; width: 100%;">
        <div style="width: 48%; margin-right: 2%;">
            <h4>Ingresos</h4>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $categorias = [
                            'señas_recibidas' => ['nombre' => 'Señas de Reservas', 'total' => 0],
                            'alquileres' => ['nombre' => 'Alquileres', 'total' => 0],
                            'multas_retraso' => ['nombre' => 'Multas', 'total' => 0],
                            'ventas' => ['nombre' => 'Ventas', 'total' => 0],
                            'ingresos_cancelaciones' => ['nombre' => 'Cancelaciones', 'total' => 0]
                        ];
                        
                        foreach ($movimientosMensuales as $semana) {
                            foreach ($categorias as $key => $value) {
                                $categorias[$key]['total'] += $semana['desglose']['ingresos'][$key];
                            }
                        }
                    @endphp
                    
                    @foreach($categorias as $key => $categoria)
                    <tr>
                        <td>{{ $categoria['nombre'] }}</td>
                        <td class="text-end">₲ {{ number_format($categoria['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-end">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="width: 48%; margin-left: 2%;">
            <h4>Egresos</h4>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $categoriasEgresos = [
                            'devoluciones_cancelaciones' => ['nombre' => 'Devoluciones por Cancelaciones', 'total' => 0],
                            'garantias_devueltas' => ['nombre' => 'Garantías Devueltas', 'total' => 0],
                            'compras' => ['nombre' => 'Compras', 'total' => 0],
                            'gastos_varios' => ['nombre' => 'Gastos Varios', 'total' => 0]
                        ];
                        
                        foreach ($movimientosMensuales as $semana) {
                            foreach ($categoriasEgresos as $key => $value) {
                                $categoriasEgresos[$key]['total'] += $semana['desglose']['egresos'][$key];
                            }
                        }
                    @endphp
                    
                    @foreach($categoriasEgresos as $key => $categoria)
                    <tr>
                        <td>{{ $categoria['nombre'] }}</td>
                        <td class="text-end">₲ {{ number_format($categoria['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>TOTAL</th>
                        <th class="text-end">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Desglose Semanal -->
    <div class="page-break"></div>
    <h3>Desglose por Semana</h3>
    
    @foreach($movimientosMensuales as $semana)
    <div class="summary-box" style="margin-bottom: 30px;">
        <div class="summary-title">Semana {{ $semana['semana'] }} ({{ $semana['fecha_inicio'] }} al {{ $semana['fecha_fin'] }})</div>
        <div style="display: flex; width: 100%;">
            <div style="width: 48%; margin-right: 2%;">
                <h4>Ingresos</h4>
                <table>
                    <tbody>
                        <tr>
                            <td>Señas de Reservas:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['ingresos']['señas_recibidas'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Alquileres:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['ingresos']['alquileres'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Multas por Retraso:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['ingresos']['multas_retraso'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ventas:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['ingresos']['ventas'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Ingresos por Cancelaciones:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['ingresos']['ingresos_cancelaciones'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Total Ingresos:</th>
                            <th class="text-end">₲ {{ number_format($semana['ingresos'], 0, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="width: 48%; margin-left: 2%;">
                <h4>Egresos</h4>
                <table>
                    <tbody>
                        <tr>
                            <td>Devoluciones por Cancelaciones:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['egresos']['devoluciones_cancelaciones'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Garantías Devueltas:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['egresos']['garantias_devueltas'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Compras:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['egresos']['compras'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Gastos Varios:</td>
                            <td class="text-end">₲ {{ number_format($semana['desglose']['egresos']['gastos_varios'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Total Egresos:</th>
                            <th class="text-end">₲ {{ number_format($semana['egresos'], 0, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top: 10px; padding: 5px; background-color: {{ $semana['saldo_neto'] >= 0 ? '#d4edda' : '#f8d7da' }}; text-align: center;">
            <strong>Saldo Neto: ₲ {{ number_format($semana['saldo_neto'], 0, ',', '.') }}</strong>
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>Este es un documento generado automáticamente por el sistema de gestión.</p>
        <p>© {{ date('Y') }} - Todos los derechos reservados</p>
    </div>
</body>
</html>