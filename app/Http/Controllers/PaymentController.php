<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Notifications\PaymentReceived;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /** Pay form for one invoice. */
    public function create(Request $request, Invoice $invoice)
    {
        $this->ownInvoice($request, $invoice);

        if ($invoice->status === 'paid') {
            return redirect()->route('dashboard');
        }

        return view('payments.create', ['invoice' => $invoice->load('lease.unit.property')]);
    }

    /** Send the STK Push and create a pending payment. */
    public function store(Request $request, Invoice $invoice, MpesaService $mpesa)
    {
        $this->ownInvoice($request, $invoice);

        $data = $request->validate([
            'phone' => ['required', 'regex:/^(\+?254|0)[17]\d{8}$/'],
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $invoice->balance],
        ], [
            'phone.regex' => 'Enter a valid Safaricom number like 0712345678 (no spaces).',
        ]);

        $phone = $this->normalizePhone($data['phone']);
        $amount = (int) round($data['amount']);

        try {
            $response = $mpesa->stkPush($phone, $amount, 'RentEase', 'Rent payment');
        } catch (\Throwable $e) {
            Log::error('STK push failed: ' . $e->getMessage());

            return back()->withInput()->withErrors(['phone' => 'Could not reach M-Pesa. Please try again.']);
        }

        if (($response['ResponseCode'] ?? null) !== '0') {
            Log::warning('STK push rejected', $response);

            return back()->withInput()->withErrors([
                'phone' => $response['errorMessage'] ?? $response['ResponseDescription'] ?? 'M-Pesa rejected the request.',
            ]);
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'tenant_id' => $request->user()->id,
            'phone' => $phone,
            'amount' => $amount,
            'checkout_request_id' => $response['CheckoutRequestID'],
            'status' => 'pending',
        ]);

        return redirect()->route('payments.show', $payment);
    }

    /** "Check your phone" page that polls for the result. */
    public function show(Request $request, Payment $payment)
    {
        abort_unless($payment->tenant_id === $request->user()->id, 403);

        return view('payments.pending', ['payment' => $payment->load('invoice')]);
    }

    /** JSON status used by the polling page. */
    public function status(Request $request, Payment $payment)
    {
        abort_unless($payment->tenant_id === $request->user()->id, 403);

        return response()->json([
            'status' => $payment->status,
            'receipt' => $payment->mpesa_receipt,
            'message' => $payment->result_desc,
        ]);
    }

    /** Safaricom posts the payment result here. */
    public function callback(Request $request)
    {
        Log::info('M-Pesa callback received', $request->all());

        $callback = $request->input('Body.stkCallback');

        if (is_array($callback)) {
            $this->handleStkCallback($callback);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * DEV ONLY (local environment): fires the callback handler with a fake payload so you
     * can test invoice updates without waiting for Safaricom. Not part of the real flow.
     */
    public function simulate(Request $request, Payment $payment, string $result = 'success')
    {
        abort_unless(app()->environment('local'), 404);
        abort_unless($payment->tenant_id === $request->user()->id, 403);

        $callback = [
            'MerchantRequestID' => 'SIMULATED',
            'CheckoutRequestID' => $payment->checkout_request_id,
            'ResultCode' => $result === 'success' ? 0 : 1032,
            'ResultDesc' => $result === 'success'
                ? 'The service request is processed successfully.'
                : 'Request cancelled by user',
        ];

        if ($result === 'success') {
            $callback['CallbackMetadata'] = ['Item' => [
                ['Name' => 'Amount', 'Value' => (float) $payment->amount],
                ['Name' => 'MpesaReceiptNumber', 'Value' => 'SIM' . strtoupper(Str::random(7))],
                ['Name' => 'PhoneNumber', 'Value' => (int) $payment->phone],
            ]];
        }

        $this->handleStkCallback($callback);

        return redirect()->route('payments.show', $payment);
    }

    private function handleStkCallback(array $callback): void
    {
        $payment = Payment::where('checkout_request_id', $callback['CheckoutRequestID'] ?? '')->first();

        // Ignore unknown payments and callbacks we have already processed
        if (! $payment || $payment->status !== 'pending') {
            return;
        }

        if ((int) ($callback['ResultCode'] ?? 1) !== 0) {
            $payment->update([
                'status' => 'failed',
                'result_desc' => $callback['ResultDesc'] ?? 'Payment failed',
            ]);

            return;
        }

        $meta = collect($callback['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');

        DB::transaction(function () use ($payment, $callback, $meta) {
            $payment->update([
                'status' => 'success',
                'mpesa_receipt' => $meta['MpesaReceiptNumber'] ?? null,
                'amount' => $meta['Amount'] ?? $payment->amount,
                'result_desc' => $callback['ResultDesc'] ?? null,
                'paid_at' => now(),
            ]);

            $invoice = Invoice::lockForUpdate()->find($payment->invoice_id);
            $invoice->balance = max(0, (float) $invoice->balance - (float) $payment->amount);
            $invoice->status = $invoice->balance <= 0 ? 'paid' : 'partial';
            $invoice->save();
        });

        // Send the receipt only after the database changes are saved
        try {
            $payment->load('invoice');
            $payment->tenant->notify(new PaymentReceived($payment));
        } catch (\Throwable $e) {
            // A mail problem must never break the payment confirmation
            Log::error('Receipt email failed: ' . $e->getMessage());
        }
    }

    private function ownInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->lease->tenant_id === $request->user()->id, 403);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '0') ? '254' . substr($digits, 1) : $digits;
    }
}
