<x-page title="New maintenance request">
    <form method="POST" action="{{ route('maintenance.store') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 max-w-xl">
        @csrf

        <p class="text-sm text-gray-700">
            <span class="text-gray-500">Unit:</span> {{ $lease->unit->property->name }}, {{ $lease->unit->unit_number }}
        </p>

        <div>
            <x-input-label for="title" value="What needs fixing?" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Details" />
            <textarea id="description" name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="priority" value="Priority" />
            <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @foreach (['low', 'medium', 'high'] as $p)
                    <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Send request</x-primary-button>
            <a href="{{ route('maintenance.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
        </div>
    </form>
</x-page>
