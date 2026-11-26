<x-page :title="$property->exists ? 'Edit property' : 'Add property'">
    <form method="POST"
          action="{{ $property->exists ? route('properties.update', $property) : route('properties.store') }}"
          class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 max-w-xl">
        @csrf
        @if ($property->exists) @method('PUT') @endif

        @if (auth()->user()->isAdmin())
            <div>
                <x-input-label for="landlord_id" value="Landlord" />
                <select id="landlord_id" name="landlord_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select landlord</option>
                    @foreach ($landlords as $landlord)
                        <option value="{{ $landlord->id }}" @selected(old('landlord_id', $property->landlord_id) == $landlord->id)>{{ $landlord->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('landlord_id')" class="mt-2" />
            </div>
        @endif

        <div>
            <x-input-label for="name" value="Property name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $property->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="location" value="Location" />
            <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $property->location)" required />
            <x-input-error :messages="$errors->get('location')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Description (optional)" />
            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $property->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('properties.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
        </div>
    </form>
</x-page>
