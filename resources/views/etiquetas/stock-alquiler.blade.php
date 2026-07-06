<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas {{ $item->nombre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        .grid { width: 100%; }
        .etiqueta {
            display: inline-block;
            width: 30%;
            margin: 0 1.5% 12px 1.5%;
            padding: 8px;
            border: 1px solid #ccc;
            text-align: center;
            vertical-align: top;
        }
        .etiqueta .nombre { font-weight: bold; margin-bottom: 2px; }
        .etiqueta .talle { margin-bottom: 6px; color: #555; }
        .etiqueta img { max-width: 100%; height: auto; }
        .etiqueta .codigo { margin-top: 4px; font-size: 9px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="grid">
        @foreach ($etiquetas as $etiqueta)
            <div class="etiqueta">
                <div class="nombre">{{ $item->nombre }}</div>
                <div class="talle">Talle: {{ $etiqueta['talle'] }}</div>
                @if ($etiqueta['png'])
                    <img src="{{ $etiqueta['png'] }}" alt="{{ $etiqueta['codigo'] }}">
                    <div class="codigo">{{ $etiqueta['codigo'] }}</div>
                @else
                    <div class="codigo">Sin código</div>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
