<x-page :title="$lease->exists ? 'Edit lease' : 'New lease'">
    <form method="POST"
          action="{{ $lease->exists ? route('leases.update', $lease) : route('leases.store') }}"
          class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 max-w-xl">
        @csrf
        @if ($lease->exists) @method('PUT') @endif

        @if ($lease->exists)
            <div class="text-sm text-gray-700 space-y-1">
                <p><span class="text-gray-500">Tenant:</span> {{ $lease->tenant->name }}</p>
                <p><span class="text-gray-500">Unit:</span> {{ $lease->unit->property->name }}, {{ $lease->unit->unit_number }}</p>
                <p><span class="text-gray-500">Start date:</span> {{ $lease->start_date->format('d M Y') }}</p>
            </div>
        @else
            <div>
                <x-input-label for="unit_id" value="Vacant unit" />
                <select id="unit_id" name="unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        onchange="document.getElementById('rent_amount').value = this.selectedOptions[0].dataset.rent || ''">
                    <option value="">Select unit</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}" data-rent="{{ (int) $u->rent_amount }}" @selected(old('unit_id') == $u->id)>
                            {{ $u->property->name }} - {{ $u->unit_number }} (KES {{ number_format($u->rent_amount) }})
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tenant_id" value="Tenant" />
                <select id="tenant_id" name="tenant_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select tenant</option>
                    @foreach ($tenants as $t)
                        <option value="{{ $t->id }}" @selected(old('tenant_id') == $t->id)>{{ $t->name }} ({{ $t->email }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('tenant_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="start_date" value="Start date" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', now()->toDateString())" required />
                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
            </div>
        @endif

        <div>
            <x-input-label for="end_date" value="End date (optional)" />
            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', $lease->end_date?->toDateString())" />
            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="rent_amount" value="Monthly rent (KES)" />
            <x-text-input id="rent_amount" name="rent_amount" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('rent_amount', $lease->rent_amount ? (int) $lease->rent_amount : '')" required />
            <x-input-error :messages="$errors->get('rent_amount')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="deposit" value="Deposit (KES)" />
            <x-text-input id="deposit" name="deposit" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('deposit', $lease->deposit ? (int) $lease->deposit : 0)" />
            <x-input-error :messages="$errors->get('deposit')" class="mt-2" />
        </div>

        @if ($lease->exists)
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="active" @selected(old('status', $lease->status) === 'active')>Active</option>
                    <option value="ended" @selected(old('status', $lease->status) === 'ended')>Ended (frees the unit)</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('leases.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
        </div>
    </form>
</x-page>
