<x-page title="Properties">
    <div class="flex flex-wrap gap-3 justify-between">
        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or location" class="rounded-md border-gray-300 text-sm">
            <x-secondary-button type="submit">Search</x-secondary-button>
        </form>
        <x-button-link href="{{ route('properties.create') }}">+ Add property</x-button-link>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Name</th>
                    <th class="p-3">Location</th>
                    @if (auth()->user()->isAdmin()) <th class="p-3">Landlord</th> @endif
                    <th class="p-3">Units</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($properties as $property)
                    <tr class="border-b last:border-0">
                        <td class="p-3 font-medium text-gray-900">{{ $property->name }}</td>
                        <td class="p-3">{{ $property->location }}</td>
                        @if (auth()->user()->isAdmin()) <td class="p-3">{{ $property->landlord->name }}</td> @endif
                        <td class="p-3">{{ $property->units_count }}</td>
                        <td class="p-3">
                            <x-row-actions :edit="route('properties.edit', $property)" :delete="route('properties.destroy', $property)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-gray-500">No properties found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $properties->links() }}</div>
</x-page>
