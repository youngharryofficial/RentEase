<x-page :title="$report['title']">
    <div class="flex flex-wrap gap-3 justify-between items-center">
        <p class="text-sm text-gray-600">{{ $report['subtitle'] }}</p>
        <div class="flex gap-2">
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 underline self-center mr-2">All reports</a>
            <x-button-link href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}">Download CSV</x-button-link>
            <x-button-link href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}">Download PDF</x-button-link>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($report['summary'] as $label => $value)
            <div class="bg-white shadow-sm sm:rounded-lg p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    @foreach ($report['headings'] as $i => $heading)
                        <th class="p-3 {{ in_array($i, $report['numeric']) ? 'text-right' : '' }}">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr class="border-b last:border-0">
                        @foreach ($row as $i => $cell)
                            <td class="p-3 {{ in_array($i, $report['numeric']) ? 'text-right' : '' }}">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($report['headings']) }}" class="p-4 text-gray-500">No data for this report.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-page>
