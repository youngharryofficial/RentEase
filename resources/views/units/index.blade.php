<x-page title="Units">
    <div class="flex flex-wrap gap-3 justify-between items-end">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search unit or type" class="rounded-md border-gray-300 text-sm">
            <select name="property_id" class="rounded-md border-gray-300 text-sm">
                <option value="">All properties</option>
                @foreach ($properties as $p)
                    <option value="{{ $p->id }}" @selected(request('property_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-md border-gray-300 text-sm">
                <option value="">Any status</option>
                <option value="vacant" @selected(request('status') === 'vacant')>Vacant</option>
                <option value="occupied" @selected(request('status') === 'occupied')>Occupied</option>
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        <x-button-link href="{{ route('units.create') }}">+ Add unit</x-button-link>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Property</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Rent</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    <tr class="border-b last:border-0">
                        <td class="p-3">{{ $unit->property->name }}</td>
                        <td class="p-3 font-medium text-gray-900">{{ $unit->unit_number }}</td>
                        <td class="p-3">{{ $unit->type }}</td>
                        <td class="p-3">KES {{ number_format($unit->rent_amount) }}</td>
                        <td class="p-3"><x-badge :value="$unit->status" /></td>
                        <td class="p-3">
                            <x-row-actions :edit="route('units.edit', $unit)" :delete="route('units.destroy', $unit)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-gray-500">No units found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $units->links() }}</div>
</x-page>
