<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.subtitle { color: #64748b; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background-color: #4338ca;
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
        .footer { margin-top: 16px; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <p class="subtitle">Generado el {{ now()->format('d/m/Y H:i') }} — SISPAA</p>

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

    <p class="footer">Total de registros: {{ count($filas) }}</p>
</body>
</html>
