<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Alquiler #{{ $alquiler->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h2 { margin: 0; }
        .meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px; border-bottom: 1px solid #eee; text-align: left; }
        .r { text-align: right; }
        .totales { margin-top: 16px; }
        .totales div { margin: 2px 0; }
        .footer { text-align: center; margin-top: 28px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sastrería Medina</h2>
        <div>Comprobante de alquiler #{{ $alquiler->id }}</div>
    </div>

    <div class="meta">
        <strong>Cliente:</strong> {{ $alquiler->cliente?->nombre ?? '—' }}<br>
        <strong>Período:</strong> {{ $alquiler->fecha_inicio->format('d/m/Y') }} — {{ $alquiler->fecha_fin->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr><th>Prenda</th><th>Código</th><th>Talle</th><th class="r">Cantidad</th></tr>
        </thead>
        <tbody>
            @foreach ($prendas as $p)
                <tr>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->codigo }}</td>
                    <td>{{ $p->talle }}</td>
                    <td class="r">{{ $p->cantidad }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <div><strong>Costo total:</strong> ₲ {{ number_format($alquiler->costo_total, 0, ',', '.') }}</div>
        <div><strong>Garantía:</strong> ₲ {{ number_format($alquiler->garantia, 0, ',', '.') }}</div>
    </div>

    <div class="footer">¡Gracias por confiar en nosotros!</div>
</body>
</html>
