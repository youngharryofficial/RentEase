@php $isAdmin = auth()->user()->isAdmin(); @endphp
<x-page :title="$user->exists ? 'Edit ' . ($isAdmin ? 'user' : 'tenant') : 'Add ' . ($isAdmin ? 'user' : 'tenant')">
    <form method="POST"
          action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}"
          class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 max-w-xl">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div>
            <x-input-label for="name" value="Full name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" value="M-Pesa phone (e.g. 0712345678)" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                          :value="old('phone', $user->phone ? '0' . substr($user->phone, 3) : '')" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        @if ($isAdmin)
            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach (['tenant', 'landlord', 'admin'] as $r)
                        <option value="{{ $r }}" @selected(old('role', $user->role ?? 'tenant') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
        @endif

        <div>
            <x-input-label for="password" :value="$user->exists ? 'New password (leave blank to keep current)' : 'Password (min 8 characters)'" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $user->exists" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
        </div>
    </form>
</x-page>
