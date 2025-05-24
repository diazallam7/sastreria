<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Venta</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Comprobante de Venta</h2>
    </div>

    <div class="details">
        <p><strong>Cliente:</strong> {{ $venta->cliente->nombre }}</p>
        <p><strong>Vestido Vendido:</strong> {{ $venta->vestido->nombre }}</p>
        <p><strong>Fecha de Venta:</strong> {{ $venta->fecha_venta }}</p>
        <p><strong>Precio Total:</strong> {{ number_format($venta->precio_total, 0, ',', '.') }} Gs.</p>
    </div>

    <div class="footer">
        <p>Gracias por confiar en nosotros. ¡Esperamos verte pronto!</p>
    </div>
</body>
</html>
