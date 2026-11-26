<x-page title="Leases">
    <div class="flex flex-wrap gap-3 justify-between items-end">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tenant or unit" class="rounded-md border-gray-300 text-sm">
            <select name="status" class="rounded-md border-gray-300 text-sm">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="ended" @selected(request('status') === 'ended')>Ended</option>
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        <x-button-link href="{{ route('leases.create') }}">+ New lease</x-button-link>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Tenant</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3">Start</th>
                    <th class="p-3">End</th>
                    <th class="p-3">Rent</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leases as $lease)
                    <tr class="border-b last:border-0">
                        <td class="p-3 font-medium text-gray-900">{{ $lease->tenant->name }}</td>
                        <td class="p-3">{{ $lease->unit->property->name }}, {{ $lease->unit->unit_number }}</td>
                        <td class="p-3">{{ $lease->start_date->format('d M Y') }}</td>
                        <td class="p-3">{{ $lease->end_date?->format('d M Y') ?? '-' }}</td>
                        <td class="p-3">KES {{ number_format($lease->rent_amount) }}</td>
                        <td class="p-3"><x-badge :value="$lease->status" /></td>
                        <td class="p-3">
                            <x-row-actions :edit="route('leases.edit', $lease)" :delete="route('leases.destroy', $lease)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-gray-500">No leases found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $leases->links() }}</div>
</x-page>
