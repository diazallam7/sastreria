<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Venta #{{ $venta->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h2 { margin: 0; }
        .meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px; border-bottom: 1px solid #eee; text-align: left; }
        .r { text-align: right; }
        .tot { font-weight: bold; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 28px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sastrería Medina</h2>
        <div>Comprobante de venta #{{ $venta->id }}</div>
    </div>

    <div class="meta">
        <strong>Cliente:</strong> {{ $venta->cliente?->nombre ?? 'Consumidor final' }}<br>
        <strong>Fecha:</strong> {{ $venta->fecha_venta->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr><th>Producto</th><th>Talle</th><th class="r">Cant.</th><th class="r">Precio</th><th class="r">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach ($venta->detalles as $d)
                <tr>
                    <td>{{ $d->nombre_producto }}</td>
                    <td>{{ $d->talle ?? '—' }}</td>
                    <td class="r">{{ $d->cantidad }}</td>
                    <td class="r">₲ {{ number_format($d->precio_unitario, 0, ',', '.') }}</td>
                    <td class="r">₲ {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="tot"><td colspan="4" class="r">Total</td><td class="r">₲ {{ number_format($venta->precio_total, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>

    <div class="footer">¡Gracias por su compra!</div>
</body>
</html>
