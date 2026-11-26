<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function show(Request $request, string $type)
    {
        $report = match ($type) {
            'collections' => $this->collections($request),
            'arrears' => $this->arrears($request),
            'occupancy' => $this->occupancy($request),
        };
        $report['type'] = $type;

        return match ($request->query('format')) {
            'csv' => $this->csv($report),
            'pdf' => $this->pdf($report),
            default => view('reports.show', compact('report')),
        };
    }

    /** Successful M-Pesa payments between two dates. */
    private function collections(Request $request): array
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);

        $from = $request->query('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->query('to') ?: now()->toDateString();

        $payments = Payment::visibleTo($request->user())
            ->with('tenant', 'invoice.lease.unit.property')
            ->where('status', 'success')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to)
            ->orderBy('paid_at')
            ->get();

        return [
            'title' => 'Rent collection report',
            'subtitle' => "From {$from} to {$to}",
            'headings' => ['Date', 'Tenant', 'Unit', 'Period', 'M-Pesa receipt', 'Amount (KES)'],
            'rows' => $payments->map(fn ($p) => [
                $p->paid_at->format('d M Y H:i'),
                $p->tenant->name,
                $p->invoice->lease->unit->property->name . ', ' . $p->invoice->lease->unit->unit_number,
                $p->invoice->period,
                $p->mpesa_receipt ?? '-',
                number_format($p->amount),
            ])->all(),
            'numeric' => [5],
            'summary' => [
                'Payments received' => $payments->count(),
                'Total collected' => 'KES ' . number_format($payments->sum('amount')),
            ],
        ];
    }

    /** Invoices that are past their due date and not fully paid. */
    private function arrears(Request $request): array
    {
        $invoices = Invoice::visibleTo($request->user())
            ->with('lease.tenant', 'lease.unit.property')
            ->where('balance', '>', 0)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        return [
            'title' => 'Arrears report',
            'subtitle' => 'Unpaid rent past its due date, as of ' . now()->format('d M Y'),
            'headings' => ['Tenant', 'Phone', 'Unit', 'Period', 'Due date', 'Days overdue', 'Balance (KES)'],
            'rows' => $invoices->map(fn ($i) => [
                $i->lease->tenant->name,
                $i->lease->tenant->phone ?? '-',
                $i->lease->unit->property->name . ', ' . $i->lease->unit->unit_number,
                $i->period,
                $i->due_date->format('d M Y'),
                (int) abs($i->due_date->diffInDays(today())),
                number_format($i->balance),
            ])->all(),
            'numeric' => [5, 6],
            'summary' => [
                'Overdue invoices' => $invoices->count(),
                'Tenants in arrears' => $invoices->pluck('lease.tenant_id')->unique()->count(),
                'Total arrears' => 'KES ' . number_format($invoices->sum('balance')),
            ],
        ];
    }

    /** Units and occupancy per property. */
    private function occupancy(Request $request): array
    {
        $properties = Property::visibleTo($request->user())->with('units')->orderBy('name')->get();

        $rows = $properties->map(function ($property) {
            $total = $property->units->count();
            $occupied = $property->units->where('status', 'occupied')->count();

            return [
                $property->name,
                $total,
                $occupied,
                $total - $occupied,
                ($total ? round($occupied / $total * 100) : 0) . '%',
                number_format($property->units->sum('rent_amount')),
                number_format($property->units->where('status', 'occupied')->sum('rent_amount')),
            ];
        });

        $units = $properties->flatMap->units;
        $occupiedUnits = $units->where('status', 'occupied');

        return [
            'title' => 'Occupancy report',
            'subtitle' => 'As of ' . now()->format('d M Y'),
            'headings' => ['Property', 'Units', 'Occupied', 'Vacant', 'Occupancy', 'Potential rent (KES)', 'Rent from occupied (KES)'],
            'rows' => $rows->all(),
            'numeric' => [1, 2, 3, 4, 5, 6],
            'summary' => [
                'Total units' => $units->count(),
                'Occupied' => $occupiedUnits->count(),
                'Overall occupancy' => ($units->count() ? round($occupiedUnits->count() / $units->count() * 100) : 0) . '%',
                'Monthly rent from occupied units' => 'KES ' . number_format($occupiedUnits->sum('rent_amount')),
            ],
        ];
    }

    private function csv(array $report)
    {
        $name = "rentease-{$report['type']}-" . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads UTF-8 correctly

            fputcsv($out, [$report['title']], ',', '"', '');
            fputcsv($out, [$report['subtitle'] ?? ''], ',', '"', '');
            fputcsv($out, [], ',', '"', '');
            fputcsv($out, $report['headings'], ',', '"', '');

            foreach ($report['rows'] as $row) {
                fputcsv($out, $row, ',', '"', '');
            }

            fputcsv($out, [], ',', '"', '');
            foreach ($report['summary'] as $label => $value) {
                fputcsv($out, [$label, $value], ',', '"', '');
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }

    private function pdf(array $report)
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'The PDF package is not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', [
            'report' => $report,
            'user' => auth()->user(),
        ])->setPaper('a4', count($report['headings']) > 6 ? 'landscape' : 'portrait');

        return $pdf->download("rentease-{$report['type']}-" . now()->format('Ymd') . '.pdf');
    }
}
