<x-page title="Invoices">
    <form method="POST" action="{{ route('invoices.generate') }}"
          class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
        @csrf
        <div>
            <x-input-label for="month" value="Generate rent invoices for" />
            <input id="month" type="month" name="month" value="{{ now()->format('Y-m') }}" required class="mt-1 rounded-md border-gray-300 text-sm">
        </div>
        <x-primary-button>Generate invoices</x-primary-button>
        <p class="text-xs text-gray-500">Creates one invoice per active lease, due on the 5th. Existing ones are skipped.</p>
    </form>

    <div class="flex flex-wrap gap-3 justify-between items-end">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tenant or period" class="rounded-md border-gray-300 text-sm">
            <select name="status" class="rounded-md border-gray-300 text-sm">
                <option value="">Any status</option>
                @foreach (['unpaid', 'partial', 'paid', 'overdue'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        <x-button-link href="{{ route('invoices.create') }}">+ New invoice</x-button-link>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Tenant</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3">Period</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">Balance</th>
                    <th class="p-3">Due</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr class="border-b last:border-0">
                        <td class="p-3 font-medium text-gray-900">{{ $inv->lease->tenant->name }}</td>
                        <td class="p-3">{{ $inv->lease->unit->property->name }}, {{ $inv->lease->unit->unit_number }}</td>
                        <td class="p-3">{{ $inv->period }}</td>
                        <td class="p-3">KES {{ number_format($inv->amount) }}</td>
                        <td class="p-3">KES {{ number_format($inv->balance) }}</td>
                        <td class="p-3">{{ $inv->due_date->format('d M Y') }}</td>
                        <td class="p-3"><x-badge :value="$inv->status" /></td>
                        <td class="p-3">
                            <x-row-actions :edit="route('invoices.edit', $inv)" :delete="route('invoices.destroy', $inv)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-4 text-gray-500">No invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $invoices->links() }}</div>
</x-page>
