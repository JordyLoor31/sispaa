<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page {
            margin: 130px 40px 80px 40px;
        }

        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #1e293b; }

        /* Encabezado institucional: se repite en cada página gracias a
           position:fixed + el hueco reservado arriba con @page margin. */
        header {
            position: fixed;
            top: -100px;
            left: 0px;
            right: 0px;
            height: 90px;
        }
        header .logo-cell {
            display: inline-block;
            vertical-align: middle;
        }
        header img { height: 42px; }
        header .facultad {
            display: inline-block;
            vertical-align: middle;
            float: right;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            color: #3c6e71;
            max-width: 260px;
            margin-top: 8px;
        }
        header .rule {
            clear: both;
            margin-top: 10px;
            border-top: 2px solid #3c6e71;
        }

        h1 { font-size: 18px; margin: 0 0 4px 0; }
        p.subtitle { color: #64748b; margin-top: 0; margin-bottom: 16px; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background-color: #3c6e71;
            color: #ffffff;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr:nth-child(even) { background-color: #f8fafc; }

        /* Pie de página institucional: igual que el header, se repite en
           cada página. El número de página se dibuja aparte vía el script
           PHP embebido de dompdf (más abajo), porque los contadores CSS
           (counter(page)) no son consistentes dentro de elementos fixed. */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
        }
        footer .rule {
            border-top: 1px solid #e2e8f0;
            margin-bottom: 6px;
        }
        footer .footer-text {
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-cell">
            <img src="{{ public_path('images/uleam.png') }}">
        </div>
        <div class="facultad">Facultad de Ciencias de la Vida<br>y Tecnologías</div>
        <div class="rule"></div>
    </header>

    <footer>
        <div class="rule"></div>
        <div class="footer-text">Documento descargado del Sistema SISPAA — Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </footer>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('Helvetica', 'normal');
            $size = 8.5;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = $pdf->get_width() - $width - 40;
            $y = $pdf->get_height() - 40;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.58, 0.64, 0.72));
        }
    </script>

    <h1>{{ $titulo }}</h1>
    <p class="subtitle">Total de registros: {{ count($filas) }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($columnas as $columna)
                    <th>{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    @foreach ($fila as $valor)
                        <td>{{ $valor ?? '—' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columnas) }}" style="text-align: center; color: #94a3b8;">Sin datos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
