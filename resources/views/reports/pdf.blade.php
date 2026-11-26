<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .muted { color: #6b7280; }
        .summary { margin: 14px 0; }
        .summary td { padding: 4px 16px 4px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: #f3f4f6; text-align: left; padding: 6px; border: 1px solid #d1d5db; }
        table.data td { padding: 6px; border: 1px solid #d1d5db; }
        .right { text-align: right; }
        .footer { margin-top: 18px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>RentEase - {{ $report['title'] }}</h1>
    <p class="muted">{{ $report['subtitle'] }}</p>

    <table class="summary">
        <tr>
            @foreach ($report['summary'] as $label => $value)
                <td><span class="muted">{{ $label }}</span><br><strong>{{ $value }}</strong></td>
            @endforeach
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach ($report['headings'] as $i => $heading)
                    <th class="{{ in_array($i, $report['numeric']) ? 'right' : '' }}">{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $i => $cell)
                        <td class="{{ in_array($i, $report['numeric']) ? 'right' : '' }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($report['headings']) }}">No data for this report.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Generated {{ now()->format('d M Y, H:i') }} by {{ $user->name }}</p>
</body>
</html>
