<x-page title="Payments">
    <div class="flex flex-wrap gap-3 justify-between items-end">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tenant or receipt" class="rounded-md border-gray-300 text-sm">
            <select name="status" class="rounded-md border-gray-300 text-sm">
                <option value="">Any status</option>
                @foreach (['success', 'pending', 'failed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-md border-gray-300 text-sm" title="From">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-md border-gray-300 text-sm" title="To">
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        <div class="bg-white shadow-sm sm:rounded-lg px-4 py-2 text-sm">
            Successful total: <strong>KES {{ number_format($total) }}</strong>
        </div>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Date</th>
                    <th class="p-3">Tenant</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3">Period</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">M-Pesa receipt</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $p)
                    <tr class="border-b last:border-0">
                        <td class="p-3">{{ ($p->paid_at ?? $p->created_at)->format('d M Y H:i') }}</td>
                        <td class="p-3 font-medium text-gray-900">{{ $p->tenant->name }}</td>
                        <td class="p-3">{{ $p->invoice->lease->unit->property->name }}, {{ $p->invoice->lease->unit->unit_number }}</td>
                        <td class="p-3">{{ $p->invoice->period }}</td>
                        <td class="p-3">KES {{ number_format($p->amount) }}</td>
                        <td class="p-3">{{ $p->mpesa_receipt ?? '-' }}</td>
                        <td class="p-3"><x-badge :value="$p->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-gray-500">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $payments->links() }}</div>
</x-page>
