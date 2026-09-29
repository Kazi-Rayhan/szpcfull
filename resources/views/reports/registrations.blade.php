<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Registrations report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        p { margin: 0 0 10px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: bold; }
        tr:nth-child(even) td { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>Registrations report</h1>
    <p>Generated {{ $generatedAt->format('M j, Y g:i A') }} · {{ count($rows) }} {{ \Illuminate\Support\Str::plural('registration', count($rows)) }}</p>
    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">No registrations match the current list.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
