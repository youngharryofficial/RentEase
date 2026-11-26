<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksVisibility;
use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    use ChecksVisibility;

    public function index(Request $request)
    {
        $invoices = Invoice::visibleTo($request->user())
            ->with('lease.tenant', 'lease.unit.property')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->q, fn ($query, $s) => $query->where(
                fn ($w) => $w->where('period', 'like', "%{$s}%")
                    ->orWhereHas('lease.tenant', fn ($t) => $t->where('name', 'like', "%{$s}%"))
            ))
            ->orderByDesc('due_date')
            ->paginate(10)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        return view('invoices.form', [
            'invoice' => new Invoice(),
            'leases' => Lease::visibleTo($request->user())->where('status', 'active')
                ->with('tenant', 'unit.property')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lease_id' => ['required', 'exists:leases,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['required', 'date'],
        ]);

        Lease::visibleTo($request->user())->findOrFail($data['lease_id']);

        $due = Carbon::parse($data['due_date']);

        Invoice::create([
            'lease_id' => $data['lease_id'],
            'period' => $due->format('F Y'),
            'amount' => $data['amount'],
            'balance' => $data['amount'],
            'due_date' => $due,
            'status' => $due->lt(today()) ? 'overdue' : 'unpaid',
        ]);

        return redirect()->route('invoices.index')->with('status', 'Invoice created.');
    }

    public function edit(Invoice $invoice)
    {
        $this->ensureVisible(Invoice::class, $invoice);

        return view('invoices.form', ['invoice' => $invoice->load('lease.tenant', 'lease.unit.property')]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->ensureVisible(Invoice::class, $invoice);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['required', 'date'],
        ]);

        $due = Carbon::parse($data['due_date']);

        $invoice->amount = $data['amount'];
        $invoice->due_date = $due;
        $invoice->period = $due->format('F Y');
        $invoice->recalculate();

        return redirect()->route('invoices.index')->with('status', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->ensureVisible(Invoice::class, $invoice);

        if ($invoice->payments()->where('status', 'success')->exists()) {
            return back()->with('error', 'This invoice has payments and cannot be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Invoice deleted.');
    }

    /** Create one invoice per active lease for the chosen month (skips ones that already exist). */
    public function generate(Request $request)
    {
        $request->validate(['month' => ['required', 'date_format:Y-m']]);

        $due = Carbon::parse($request->month . '-05')->startOfDay();
        $period = $due->format('F Y');
        $created = 0;

        $leases = Lease::visibleTo($request->user())->where('status', 'active')->get();

        foreach ($leases as $lease) {
            if (Invoice::where('lease_id', $lease->id)->where('period', $period)->exists()) {
                continue;
            }

            Invoice::create([
                'lease_id' => $lease->id,
                'period' => $period,
                'amount' => $lease->rent_amount,
                'balance' => $lease->rent_amount,
                'due_date' => $due,
                'status' => $due->lt(today()) ? 'overdue' : 'unpaid',
            ]);
            $created++;
        }

        return redirect()->route('invoices.index')
            ->with('status', "Created {$created} invoice(s) for {$period}.");
    }
}
