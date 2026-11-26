<x-page title="Reports">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <form method="GET" action="{{ route('reports.show', 'collections') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-800">Rent collection</h3>
                <p class="text-sm text-gray-500">Successful M-Pesa payments received in a date range.</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="from" value="From" />
                    <input id="from" type="date" name="from" value="{{ now()->startOfMonth()->toDateString() }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <x-input-label for="to" value="To" />
                    <input id="to" type="date" name="to" value="{{ now()->toDateString() }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-primary-button>View</x-primary-button>
                <x-secondary-button type="submit" name="format" value="csv">CSV</x-secondary-button>
                <x-secondary-button type="submit" name="format" value="pdf">PDF</x-secondary-button>
            </div>
        </form>

        <form method="GET" action="{{ route('reports.show', 'arrears') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-800">Arrears</h3>
                <p class="text-sm text-gray-500">Tenants with unpaid rent past its due date, and how much they owe.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-primary-button>View</x-primary-button>
                <x-secondary-button type="submit" name="format" value="csv">CSV</x-secondary-button>
                <x-secondary-button type="submit" name="format" value="pdf">PDF</x-secondary-button>
            </div>
        </form>

        <form method="GET" action="{{ route('reports.show', 'occupancy') }}" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-800">Occupancy</h3>
                <p class="text-sm text-gray-500">Occupied and vacant units per property, with potential rent.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-primary-button>View</x-primary-button>
                <x-secondary-button type="submit" name="format" value="csv">CSV</x-secondary-button>
                <x-secondary-button type="submit" name="format" value="pdf">PDF</x-secondary-button>
            </div>
        </form>
    </div>
</x-page>
