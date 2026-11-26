@php $manager = ! auth()->user()->isTenant(); @endphp
<x-page title="Maintenance requests">
    <div class="flex flex-wrap gap-3 justify-between items-end">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or tenant" class="rounded-md border-gray-300 text-sm">
            <select name="status" class="rounded-md border-gray-300 text-sm">
                <option value="">Any status</option>
                @foreach (['open', 'in_progress', 'resolved'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="rounded-md border-gray-300 text-sm">
                <option value="">Any priority</option>
                @foreach (['low', 'medium', 'high'] as $p)
                    <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        @unless ($manager)
            <x-button-link href="{{ route('maintenance.create') }}">+ New request</x-button-link>
        @endunless
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Date</th>
                    @if ($manager) <th class="p-3">Tenant</th> @endif
                    <th class="p-3">Unit</th>
                    <th class="p-3">Request</th>
                    <th class="p-3">Priority</th>
                    <th class="p-3">Status</th>
                    @if ($manager) <th class="p-3"></th> @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $r)
                    <tr class="border-b last:border-0 align-top">
                        <td class="p-3 whitespace-nowrap">{{ $r->created_at->format('d M Y') }}</td>
                        @if ($manager) <td class="p-3">{{ $r->tenant->name }}</td> @endif
                        <td class="p-3">{{ $r->unit->property->name }}, {{ $r->unit->unit_number }}</td>
                        <td class="p-3">
                            <p class="font-medium text-gray-900">{{ $r->title }}</p>
                            <p class="text-gray-500">{{ $r->description }}</p>
                        </td>
                        <td class="p-3"><x-badge :value="$r->priority" /></td>
                        <td class="p-3">
                            @if ($manager)
                                <form method="POST" action="{{ route('maintenance.update', $r) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-md border-gray-300 text-sm">
                                        @foreach (['open', 'in_progress', 'resolved'] as $s)
                                            <option value="{{ $s }}" @selected($r->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                        @endforeach
                                    </select>
                                    <x-secondary-button type="submit">Save</x-secondary-button>
                                </form>
                            @else
                                <x-badge :value="$r->status" />
                            @endif
                        </td>
                        @if ($manager)
                            <td class="p-3"><x-row-actions :delete="route('maintenance.destroy', $r)" /></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-gray-500">No maintenance requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $requests->links() }}</div>
</x-page>
