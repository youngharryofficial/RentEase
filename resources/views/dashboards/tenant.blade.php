<x-page title="My rental dashboard" :subtitle="'Welcome back, ' . auth()->user()->name">

    <div class="rounded-xl p-6 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%);">
        @if ($lease)
            <p class="text-indigo-200 text-sm">Your home</p>
            <h3 class="text-2xl font-bold mt-1">{{ $lease->unit->property->name }}, {{ $lease->unit->unit_number }}</h3>
            <p class="text-indigo-200 text-sm">{{ $lease->unit->property->location }}</p>
            <div class="mt-4 flex flex-wrap gap-x-10 gap-y-3">
                <div>
                    <p class="text-indigo-200 text-xs uppercase tracking-wide">Monthly rent</p>
                    <p class="text-lg font-semibold">KES {{ number_format($lease->rent_amount) }}</p>
                </div>
                <div>
                    <p class="text-indigo-200 text-xs uppercase tracking-wide">Lease started</p>
                    <p class="text-lg font-semibold">{{ $lease->start_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-indigo-200 text-xs uppercase tracking-wide">Balance due</p>
                    <p class="text-lg font-semibold">KES {{ number_format($balance) }}</p>
                </div>
            </div>
            @if ($unpaid->isNotEmpty())
                <a href="{{ route('payments.create', $unpaid->first()) }}"
                   class="mt-5 inline-flex items-center px-5 py-2.5 rounded-md bg-white text-indigo-700 font-semibold text-sm hover:bg-indigo-50">
                    Pay {{ $unpaid->first()->period }} with M-Pesa
                </a>
            @else
                <p class="mt-5 inline-flex items-center gap-2 text-sm text-green-200"><x-icon name="check" class="w-5 h-5" /> You are all paid up.</p>
            @endif
        @else
            <p class="font-semibold">You do not have an active lease yet.</p>
            <p class="text-indigo-200 text-sm mt-1">Your landlord will assign you a unit.</p>
        @endif
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <h3 class="font-semibold text-gray-900 p-6 pb-2">Unpaid invoices</h3>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left">
                    <th class="p-3 pl-6">Period</th>
                    <th class="p-3">Balance</th>
                    <th class="p-3">Due date</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 pr-6"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($unpaid as $inv)
                    <tr class="border-t">
                        <td class="p-3 pl-6 font-medium text-gray-900">{{ $inv->period }}</td>
                        <td class="p-3">KES {{ number_format($inv->balance) }}</td>
                        <td class="p-3">{{ $inv->due_date->format('d M Y') }}</td>
                        <td class="p-3"><x-badge :value="$inv->status" /></td>
                        <td class="p-3 pr-6 text-right">
                            <a href="{{ route('payments.create', $inv) }}" class="inline-flex px-3 py-1 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500">Pay now</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 pl-6 text-gray-500">Nothing to pay right now.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Recent payments</h3>
            <ul class="divide-y text-sm">
                @forelse ($payments as $p)
                    <li class="py-2.5 flex justify-between gap-3">
                        <span>
                            <span class="font-medium text-gray-900">{{ $p->invoice->period }}</span>
                            <span class="block text-xs text-gray-500">{{ $p->mpesa_receipt ?? 'pending' }}</span>
                        </span>
                        <span class="text-right">
                            KES {{ number_format($p->amount) }}
                            <span class="block"><x-badge :value="$p->status" /></span>
                        </span>
                    </li>
                @empty
                    <li class="py-2 text-gray-500">No payments yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-900">My maintenance requests</h3>
                @if ($lease)
                    <a href="{{ route('maintenance.create') }}" class="text-sm text-indigo-600 hover:underline">New request</a>
                @endif
            </div>
            <ul class="divide-y text-sm">
                @forelse ($requests as $r)
                    <li class="py-2.5 flex justify-between gap-3">
                        <span class="font-medium text-gray-900">{{ $r->title }}</span>
                        <x-badge :value="$r->status" />
                    </li>
                @empty
                    <li class="py-2 text-gray-500">No requests.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-page>
