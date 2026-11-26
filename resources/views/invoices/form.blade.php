<x-page :title="$invoice->exists ? 'Edit invoice' : 'New invoice'">
    <form method="POST"
          action="{{ $invoice->exists ? route('invoices.update', $invoice) : route('invoices.store') }}"
          class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 max-w-xl">
        @csrf
        @if ($invoice->exists) @method('PUT') @endif

        @if ($invoice->exists)
            <div class="text-sm text-gray-700 space-y-1">
                <p><span class="text-gray-500">Tenant:</span> {{ $invoice->lease->tenant->name }}</p>
                <p><span class="text-gray-500">Unit:</span> {{ $invoice->lease->unit->property->name }}, {{ $invoice->lease->unit->unit_number }}</p>
                <p><span class="text-gray-500">Period:</span> {{ $invoice->period }}</p>
            </div>
        @else
            <div>
                <x-input-label for="lease_id" value="Lease" />
                <select id="lease_id" name="lease_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        onchange="document.getElementById('amount').value = this.selectedOptions[0].dataset.rent || ''">
                    <option value="">Select active lease</option>
                    @foreach ($leases as $l)
                        <option value="{{ $l->id }}" data-rent="{{ (int) $l->rent_amount }}" @selected(old('lease_id') == $l->id)>
                            {{ $l->tenant->name }} - {{ $l->unit->property->name }}, {{ $l->unit->unit_number }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('lease_id')" class="mt-2" />
            </div>
        @endif

        <div>
            <x-input-label for="amount" value="Amount (KES)" />
            <x-text-input id="amount" name="amount" type="number" min="1" step="1" class="mt-1 block w-full" :value="old('amount', $invoice->amount ? (int) $invoice->amount : '')" required />
            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="due_date" value="Due date" />
            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $invoice->due_date?->toDateString())" required />
            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('invoices.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
        </div>
    </form>
</x-page>
