<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Alquiler</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Comprobante de Alquiler</h2>
    </div>

    <div class="details">
        <p><strong>Cliente:</strong> {{ $alquiler->cliente->nombre }}</p>
        <p><strong>Vestido Alquilado:</strong> {{ $alquiler->vestido->nombre }}</p>
        <p><strong>Fechas:</strong> Desde {{ $alquiler->fecha_inicio }} hasta {{ $alquiler->fecha_fin }}</p>
        <p><strong>Costo Total:</strong> {{ number_format($alquiler->costo_total, 0, ',', '.') }} Gs.</p>
    </div>

    <div class="footer">
        <p>Gracias por confiar en nosotros. ¡Esperamos verte pronto!</p>
    </div>
</body>
</html>
