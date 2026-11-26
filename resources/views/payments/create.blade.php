<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pay rent with M-Pesa</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">

                <div class="text-sm text-gray-700 space-y-1">
                    <p><span class="text-gray-500">Unit:</span> {{ $invoice->lease->unit->property->name }}, {{ $invoice->lease->unit->unit_number }}</p>
                    <p><span class="text-gray-500">Period:</span> {{ $invoice->period }}</p>
                    <p><span class="text-gray-500">Balance due:</span> <strong>KES {{ number_format($invoice->balance) }}</strong></p>
                </div>

                <form method="POST" action="{{ route('payments.store', $invoice) }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="phone" value="M-Pesa phone number" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                      :value="old('phone', auth()->user()->phone ? '0' . substr(auth()->user()->phone, 3) : '')"
                                      placeholder="0712345678" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" value="Amount (KES)" />
                        <x-text-input id="amount" name="amount" type="number" min="1" max="{{ (int) $invoice->balance }}"
                                      class="mt-1 block w-full" :value="old('amount', (int) $invoice->balance)" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Pay with M-Pesa</x-primary-button>
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
