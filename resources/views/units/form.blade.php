<x-page :title="$unit->exists ? 'Edit unit' : 'Add unit'">
    <form method="POST"
          action="{{ $unit->exists ? route('units.update', $unit) : route('units.store') }}"
          class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 max-w-xl">
        @csrf
        @if ($unit->exists) @method('PUT') @endif

        <div>
            <x-input-label for="property_id" value="Property" />
            <select id="property_id" name="property_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Select property</option>
                @foreach ($properties as $p)
                    <option value="{{ $p->id }}" @selected(old('property_id', $unit->property_id) == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="unit_number" value="Unit number (e.g. A1)" />
            <x-text-input id="unit_number" name="unit_number" type="text" class="mt-1 block w-full" :value="old('unit_number', $unit->unit_number)" required />
            <x-input-error :messages="$errors->get('unit_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="type" value="Type" />
            <x-text-input id="type" name="type" type="text" list="unit-types" class="mt-1 block w-full" :value="old('type', $unit->type ?? 'Bedsitter')" required />
            <datalist id="unit-types">
                <option value="Bedsitter"><option value="1 Bedroom"><option value="2 Bedroom"><option value="3 Bedroom"><option value="Shop">
            </datalist>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="rent_amount" value="Monthly rent (KES)" />
            <x-text-input id="rent_amount" name="rent_amount" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('rent_amount', $unit->rent_amount)" required />
            <x-input-error :messages="$errors->get('rent_amount')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('units.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
        </div>
    </form>
</x-page>
