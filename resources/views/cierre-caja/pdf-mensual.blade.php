<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin: 0; }
        .sub { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td, th { padding: 5px 6px; border-bottom: 1px solid #eee; text-align: left; }
        .r { text-align: right; }
        .tot { font-weight: bold; border-top: 2px solid #333; }
        .green { color: #166534; }
        .red { color: #991b1b; }
    </style>
</head>
<body>
    <h1>Sastrería Medina — Cierre mensual</h1>
    <div class="sub">{{ $nombreMes }} {{ $año }}</div>

    <table>
        <thead>
            <tr><th>Semana</th><th>Período</th><th class="r">Ingresos</th><th class="r">Egresos</th><th class="r">Saldo</th></tr>
        </thead>
        <tbody>
            @foreach ($movimientosMensuales as $s)
                <tr>
                    <td>Semana {{ $s['semana'] }}</td>
                    <td>{{ $s['fecha_inicio'] }} — {{ $s['fecha_fin'] }}</td>
                    <td class="r green">₲ {{ number_format($s['ingresos'], 0, ',', '.') }}</td>
                    <td class="r red">₲ {{ number_format($s['egresos'], 0, ',', '.') }}</td>
                    <td class="r">₲ {{ number_format($s['saldo_neto'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="tot">
                <td colspan="2">Total</td>
                <td class="r green">₲ {{ number_format($totalesMes['ingresos'], 0, ',', '.') }}</td>
                <td class="r red">₲ {{ number_format($totalesMes['egresos'], 0, ',', '.') }}</td>
                <td class="r">₲ {{ number_format($totalesMes['saldo_neto'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:16px">
        <strong>Mejor semana:</strong> {{ $mejorSemana['numero'] ? 'Semana ' . $mejorSemana['numero'] : '—' }} (₲ {{ number_format(max(0, $mejorSemana['saldo']), 0, ',', '.') }})<br>
        <strong>Promedio semanal (saldo):</strong> ₲ {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}
    </p>
</body>
</html>
