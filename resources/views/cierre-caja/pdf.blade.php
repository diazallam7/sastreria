<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin: 0; }
        .sub { color: #666; margin-bottom: 16px; }
        h2 { font-size: 13px; margin: 16px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 5px 6px; border-bottom: 1px solid #eee; }
        .r { text-align: right; }
        .tot { font-weight: bold; border-top: 2px solid #333; }
        .green { color: #166534; }
        .red { color: #991b1b; }
        .saldo { margin-top: 18px; padding: 10px; background: #f5f5f5; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Sastrería Medina — Cierre de caja</h1>
    <div class="sub">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div>

    <h2>Ingresos</h2>
    <table>
        <tr><td>Alquileres</td><td class="r">₲ {{ number_format($movimientos['ingresos']['alquileres'], 0, ',', '.') }}</td></tr>
        <tr><td>Multas por retraso</td><td class="r">₲ {{ number_format($movimientos['ingresos']['multas_retraso'], 0, ',', '.') }}</td></tr>
        <tr><td>Ventas</td><td class="r">₲ {{ number_format($movimientos['ingresos']['ventas'], 0, ',', '.') }}</td></tr>
        <tr><td>Cancelaciones (neto)</td><td class="r">₲ {{ number_format($movimientos['ingresos']['ingresos_cancelaciones'], 0, ',', '.') }}</td></tr>
        <tr class="tot"><td>Total ingresos</td><td class="r green">₲ {{ number_format($movimientos['ingresos']['total'], 0, ',', '.') }}</td></tr>
    </table>

    <h2>Egresos</h2>
    <table>
        <tr><td>Compras</td><td class="r">₲ {{ number_format($movimientos['egresos']['compras'], 0, ',', '.') }}</td></tr>
        <tr><td>Gastos varios</td><td class="r">₲ {{ number_format($movimientos['egresos']['gastos_varios'], 0, ',', '.') }}</td></tr>
        <tr class="tot"><td>Total egresos</td><td class="r red">₲ {{ number_format($movimientos['egresos']['total'], 0, ',', '.') }}</td></tr>
    </table>

    <div class="saldo">Saldo neto: ₲ {{ number_format($movimientos['saldo_neto'], 0, ',', '.') }}</div>
</body>
</html>
