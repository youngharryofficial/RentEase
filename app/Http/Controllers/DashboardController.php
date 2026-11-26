<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => $this->admin(),
            'landlord' => $this->landlord($user),
            default => $this->tenant($user),
        };
    }

    private function admin()
    {
        $monthly = Payment::where('status', 'success')
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $units = Unit::count();
        $occupied = Unit::where('status', 'occupied')->count();

        return view('dashboards.admin', [
            'totalUsers' => User::count(),
            'landlords' => User::where('role', 'landlord')->count(),
            'tenants' => User::where('role', 'tenant')->count(),
            'properties' => Property::count(),
            'units' => $units,
            'occupied' => $occupied,
            'occupancy' => $units ? round($occupied / $units * 100) : 0,
            'revenue' => Payment::where('status', 'success')->sum('amount'),
            'outstanding' => Invoice::where('status', '!=', 'paid')->sum('balance'),
            'labels' => $monthly->pluck('month'),
            'totals' => $monthly->pluck('total'),
            'recentPayments' => Payment::with('tenant', 'invoice')->latest()->limit(5)->get(),
        ]);
    }

    private function landlord(User $user)
    {
        $own = fn ($q) => $q->where('landlord_id', $user->id);

        $unitsQ = Unit::whereHas('property', $own);
        $invoicesQ = Invoice::whereHas('lease.unit.property', $own);

        $units = (clone $unitsQ)->count();
        $occupied = (clone $unitsQ)->where('status', 'occupied')->count();

        return view('dashboards.landlord', [
            'units' => $units,
            'occupied' => $occupied,
            'vacant' => $units - $occupied,
            'expected' => (clone $invoicesQ)
                ->whereMonth('due_date', now()->month)
                ->whereYear('due_date', now()->year)
                ->sum('amount'),
            'collected' => Payment::where('status', 'success')
                ->whereHas('invoice.lease.unit.property', $own)
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
            'outstanding' => (clone $invoicesQ)->where('status', '!=', 'paid')->sum('balance'),
            'openRequests' => MaintenanceRequest::whereHas('unit.property', $own)
                ->where('status', '!=', 'resolved')->count(),
            'overdue' => (clone $invoicesQ)
                ->with('lease.tenant', 'lease.unit.property')
                ->where('status', '!=', 'paid')
                ->whereDate('due_date', '<', now())
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
        ]);
    }

    private function tenant(User $user)
    {
        $unpaid = Invoice::whereHas('lease', fn ($q) => $q->where('tenant_id', $user->id))
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get();

        return view('dashboards.tenant', [
            'lease' => $user->leases()->where('status', 'active')->with('unit.property')->first(),
            'unpaid' => $unpaid,
            'balance' => $unpaid->sum('balance'),
            'payments' => $user->payments()->with('invoice')->latest()->limit(5)->get(),
            'requests' => $user->maintenanceRequests()->with('unit')->latest()->limit(5)->get(),
        ]);
    }
}
