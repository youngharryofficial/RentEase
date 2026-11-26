<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::visibleTo($request->user())
            ->with('tenant', 'invoice.lease.unit.property')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->from, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->to, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($request->q, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('mpesa_receipt', 'like', "%{$s}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$s}%"))
            ));

        $total = (clone $query)->where('status', 'success')->sum('amount');

        return view('payments.index', [
            'payments' => $query->latest()->paginate(10)->withQueryString(),
            'total' => $total,
        ]);
    }
}
