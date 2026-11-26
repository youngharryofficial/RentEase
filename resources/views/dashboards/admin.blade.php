<x-page title="Admin dashboard" :subtitle="'Welcome back, ' . auth()->user()->name">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <x-stat label="Total users" :value="$totalUsers" icon="users" color="indigo" :hint="$landlords . ' landlords, ' . $tenants . ' tenants'" />
        <x-stat label="Properties" :value="$properties" icon="building" color="sky" :hint="$units . ' units'" />
        <x-stat label="Occupancy" :value="$occupancy . '%'" icon="home" color="green" :hint="$occupied . ' of ' . $units . ' units occupied'" />
        <x-stat label="Revenue collected" :value="'KES ' . number_format($revenue)" icon="banknotes" color="green" />
        <x-stat label="Outstanding rent" :value="'KES ' . number_format($outstanding)" icon="alert" color="red" />
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Monthly revenue (KES)</h3>
        <div class="h-72"><canvas id="revenueChart"></canvas></div>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <div class="flex justify-between items-center p-6 pb-2">
            <h3 class="font-semibold text-gray-900">Recent payments</h3>
            <a href="{{ route('payments.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left">
                    <th class="p-3 pl-6">Tenant</th>
                    <th class="p-3">Period</th>
                    <th class="p-3">Amount</th>
                    <th class="p-3">M-Pesa receipt</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentPayments as $p)
                    <tr class="border-t">
                        <td class="p-3 pl-6 font-medium text-gray-900">{{ $p->tenant->name }}</td>
                        <td class="p-3">{{ $p->invoice->period }}</td>
                        <td class="p-3">KES {{ number_format($p->amount) }}</td>
                        <td class="p-3">{{ $p->mpesa_receipt ?? '-' }}</td>
                        <td class="p-3"><x-badge :value="$p->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 pl-6 text-gray-500">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{ label: 'Rent collected', data: @json($totals), backgroundColor: '#6366f1', borderRadius: 6 }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    </script>
</x-page>
