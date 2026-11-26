<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">M-Pesa payment</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center space-y-4">

                <p class="text-sm text-gray-500">{{ $payment->invoice->period }} rent - KES {{ number_format($payment->amount) }}</p>

                <h3 id="title" class="text-xl font-semibold text-gray-900">Check your phone</h3>
                <p id="message" class="text-gray-600">
                    An M-Pesa prompt was sent to {{ $payment->phone }}. Enter your M-Pesa PIN to complete the payment.
                </p>

                <div id="actions" class="hidden pt-2">
                    <a href="{{ route('dashboard') }}" class="inline-block px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Back to dashboard</a>
                </div>

                @if (app()->environment('local') && $payment->status === 'pending')
                    <div class="pt-4 border-t text-xs text-gray-500">
                        Local testing only:
                        <a class="underline" href="{{ route('dev.mpesa.simulate', [$payment, 'success']) }}">simulate success</a> |
                        <a class="underline" href="{{ route('dev.mpesa.simulate', [$payment, 'fail']) }}">simulate failure</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const statusUrl = @json(route('payments.status', $payment));
        const title = document.getElementById('title');
        const message = document.getElementById('message');
        const actions = document.getElementById('actions');
        let tries = 0;

        const timer = setInterval(async () => {
            tries++;
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                if (data.status === 'success') {
                    title.textContent = 'Payment received';
                    message.textContent = 'M-Pesa receipt: ' + (data.receipt ?? '-') + '. Thank you!';
                    actions.classList.remove('hidden');
                    clearInterval(timer);
                } else if (data.status === 'failed') {
                    title.textContent = 'Payment not completed';
                    message.textContent = data.message ?? 'The payment was cancelled or failed. You can try again.';
                    actions.classList.remove('hidden');
                    clearInterval(timer);
                }
            } catch (e) { /* keep polling */ }

            if (tries >= 60) {
                message.textContent = 'Still waiting for confirmation. If you completed the payment, it will appear on your dashboard shortly.';
                actions.classList.remove('hidden');
                clearInterval(timer);
            }
        }, 3000);
    </script>
</x-app-layout>
