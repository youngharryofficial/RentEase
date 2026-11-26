@php $progress = $expected > 0 ? min(100, round($collected / $expected * 100)) : 0; @endphp
<x-page title="Landlord dashboard" :subtitle="'Welcome back, ' . auth()->user()->name">
    <div class="flex flex-wrap gap-2">
        <x-button-link href="{{ route('invoices.index') }}">Generate invoices</x-button-link>
        <x-button-link href="{{ route('properties.create') }}">Add property</x-button-link>
        <x-button-link href="{{ route('leases.create') }}">New lease</x-button-link>
        <x-button-link href="{{ route('reports.index') }}">Reports</x-button-link>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stat label="Total units" :value="$units" icon="building" color="sky" />
        <x-stat label="Occupied" :value="$occupied" icon="home" color="green" :hint="$vacant . ' vacant'" />
        <x-stat label="Total outstanding" :value="'KES ' . number_format($outstanding)" icon="alert" color="red" />
        <x-stat label="Open maintenance requests" :value="$openRequests" icon="wrench" color="amber" />
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-wrap justify-between gap-2">
            <h3 class="font-semibold text-gray-900">This month's collection</h3>
            <p class="text-sm text-gray-500">KES {{ number_format($collected) }} of KES {{ number_format($expected) }}</p>
        </div>
        <div class="mt-3 w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="bg-green-500 h-3 rounded-full" style="width: {{ $progress }}%"></div>
        </div>
        <p class="mt-2 text-xs text-gray-500">{{ $progress }}% collected</p>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <div class="flex justify-between items-center p-6 pb-2">
            <h3 class="font-semibold text-gray-900">Tenants in arrears</h3>
            <a href="{{ route('reports.show', 'arrears') }}" class="text-sm text-indigo-600 hover:underline">Full report</a>
        </div>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left">
                    <th class="p-3 pl-6">Tenant</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3">Period</th>
                    <th class="p-3">Balance</th>
                    <th class="p-3">Due date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($overdue as $inv)
                    <tr class="border-t">
                        <td class="p-3 pl-6 font-medium text-gray-900">{{ $inv->lease->tenant->name }}</td>
                        <td class="p-3">{{ $inv->lease->unit->property->name }}, {{ $inv->lease->unit->unit_number }}</td>
                        <td class="p-3">{{ $inv->period }}</td>
                        <td class="p-3">KES {{ number_format($inv->balance) }}</td>
                        <td class="p-3">{{ $inv->due_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 pl-6 text-gray-500">No overdue invoices.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-page>
