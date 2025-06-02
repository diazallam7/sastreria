<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Semanal de Caja</title>
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
        <h1>Resumen Semanal de Caja</h1>
        <p>Período: {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Resumen Principal -->
    <div class="summary-box">
        <div class="summary-title">Resumen General de la Semana</div>
        <div class="summary-content">
            <div class="summary-item">
                <p><strong>Total Ingresos:</strong></p>
                <p class="text-success">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Total Egresos:</strong></p>
                <p class="text-danger">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Saldo Neto:</strong></p>
                <p class="{{ $totalesSemana['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ₲ {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Tabla de Resumen Diario -->
    <h3>Detalle Diario de la Semana</h3>
    <table>
        <thead>
            <tr>
                <th>Día</th>
                <th>Fecha</th>
                <th class="text-end">Ingresos</th>
                <th class="text-end">Egresos</th>
                <th class="text-end">Saldo Neto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumenSemanal as $dia)
            <tr>
                <td><strong>{{ $dia['dia'] }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td>
                <td class="text-end">₲ {{ number_format($dia['ingresos'], 0, ',', '.') }}</td>
                <td class="text-end">₲ {{ number_format($dia['egresos'], 0, ',', '.') }}</td>
                <td class="text-end {{ $dia['saldo_neto'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ₲ {{ number_format($dia['saldo_neto'], 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">TOTALES</th>
                <th class="text-end">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</th>
                <th class="text-end">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</th>
                <th class="text-end">₲ {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Estadísticas Adicionales -->
    <div class="summary-box">
        <div class="summary-title">Estadísticas de la Semana</div>
        <div class="summary-content">
            <div class="summary-item">
                <p><strong>Mejor día:</strong></p>
                <p>{{ $mejorDia['dia'] }} {{ \Carbon\Carbon::parse($mejorDia['fecha'])->format('d/m/Y') }}</p>
                <p class="text-success">₲ {{ number_format($mejorDia['saldo'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Peor día:</strong></p>
                <p>{{ $peorDia['dia'] }} {{ \Carbon\Carbon::parse($peorDia['fecha'])->format('d/m/Y') }}</p>
                <p class="text-danger">₲ {{ number_format($peorDia['saldo'], 0, ',', '.') }}</p>
            </div>
            <div class="summary-item">
                <p><strong>Promedios diarios:</strong></p>
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
                        $totalIngresos = 0;
                        $categorias = [
                            'señas_recibidas' => ['nombre' => 'Señas de Reservas', 'total' => 0],
                            'alquileres' => ['nombre' => 'Alquileres', 'total' => 0],
                            'multas_retraso' => ['nombre' => 'Multas', 'total' => 0],
                            'ventas' => ['nombre' => 'Ventas', 'total' => 0],
                            'ingresos_cancelaciones' => ['nombre' => 'Cancelaciones', 'total' => 0]
                        ];
                        
                        foreach ($resumenSemanal as $dia) {
                            foreach ($categorias as $key => $value) {
                                $categorias[$key]['total'] += $dia['desglose_ingresos'][$key];
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
                        <th class="text-end">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</th>
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
                        
                        foreach ($resumenSemanal as $dia) {
                            foreach ($categoriasEgresos as $key => $value) {
                                $categoriasEgresos[$key]['total'] += $dia['desglose_egresos'][$key];
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
                        <th class="text-end">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>Este es un documento generado automáticamente por el sistema de gestión.</p>
        <p>© {{ date('Y') }} - Todos los derechos reservados</p>
    </div>
</body>
</html>