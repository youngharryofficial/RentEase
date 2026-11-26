@php $isAdmin = auth()->user()->isAdmin(); @endphp
<x-page :title="$isAdmin ? 'Users' : 'Tenants'">
    <div class="flex flex-wrap gap-3 justify-between items-end">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email or phone" class="rounded-md border-gray-300 text-sm">
            @if ($isAdmin)
                <select name="role" class="rounded-md border-gray-300 text-sm">
                    <option value="">All roles</option>
                    @foreach (['admin', 'landlord', 'tenant'] as $r)
                        <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            @endif
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>
        <x-button-link href="{{ route('users.create') }}">+ Add {{ $isAdmin ? 'user' : 'tenant' }}</x-button-link>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Phone</th>
                    @if ($isAdmin) <th class="p-3">Role</th> @endif
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr class="border-b last:border-0">
                        <td class="p-3 font-medium text-gray-900">{{ $u->name }}</td>
                        <td class="p-3">{{ $u->email }}</td>
                        <td class="p-3">{{ $u->phone ?? '-' }}</td>
                        @if ($isAdmin) <td class="p-3">{{ ucfirst($u->role) }}</td> @endif
                        <td class="p-3">
                            <x-row-actions :edit="route('users.edit', $u)"
                                           :delete="$u->id !== auth()->id() ? route('users.destroy', $u) : null" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-gray-500">Nobody found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $users->links() }}</div>
</x-page>
