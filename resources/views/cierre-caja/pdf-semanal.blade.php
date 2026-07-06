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
    <h1>Sastrería Medina — Cierre semanal</h1>
    <div class="sub">{{ $fechaInicio->format('d/m/Y') }} — {{ $fechaFin->format('d/m/Y') }}</div>

    <table>
        <thead>
            <tr><th>Día</th><th class="r">Ingresos</th><th class="r">Egresos</th><th class="r">Saldo</th></tr>
        </thead>
        <tbody>
            @foreach ($resumenSemanal as $d)
                <tr>
                    <td>{{ $d['dia'] }} {{ \Carbon\Carbon::parse($d['fecha'])->format('d/m') }}</td>
                    <td class="r green">₲ {{ number_format($d['ingresos'], 0, ',', '.') }}</td>
                    <td class="r red">₲ {{ number_format($d['egresos'], 0, ',', '.') }}</td>
                    <td class="r">₲ {{ number_format($d['saldo_neto'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="tot">
                <td>Total</td>
                <td class="r green">₲ {{ number_format($totalesSemana['ingresos'], 0, ',', '.') }}</td>
                <td class="r red">₲ {{ number_format($totalesSemana['egresos'], 0, ',', '.') }}</td>
                <td class="r">₲ {{ number_format($totalesSemana['saldo_neto'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:16px">
        <strong>Mejor día:</strong> {{ $mejorDia['dia'] ?: '—' }} (₲ {{ number_format(max(0, $mejorDia['saldo']), 0, ',', '.') }})<br>
        <strong>Promedio diario (saldo):</strong> ₲ {{ number_format($promedios['saldo_neto'], 0, ',', '.') }}
    </p>
</body>
</html>
