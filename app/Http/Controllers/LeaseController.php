<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksVisibility;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeaseController extends Controller
{
    use ChecksVisibility;

    public function index(Request $request)
    {
        $leases = Lease::visibleTo($request->user())
            ->with('unit.property', 'tenant')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->q, fn ($query, $s) => $query->where(
                fn ($w) => $w->whereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', "%{$s}%"))
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('leases.index', compact('leases'));
    }

    public function create(Request $request)
    {
        return view('leases.form', [
            'lease' => new Lease(),
            'units' => Unit::visibleTo($request->user())->where('status', 'vacant')->with('property')->get(),
            'tenants' => User::where('role', 'tenant')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'tenant_id' => ['required', Rule::exists('users', 'id')->where('role', 'tenant')],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $unit = Unit::visibleTo($request->user())->findOrFail($data['unit_id']);

        if ($unit->status !== 'vacant') {
            return back()->withInput()->withErrors(['unit_id' => 'This unit is already occupied.']);
        }

        DB::transaction(function () use ($data, $unit) {
            Lease::create(array_merge($data, [
                'deposit' => $data['deposit'] ?? 0,
                'status' => 'active',
            ]));
            $unit->update(['status' => 'occupied']);
        });

        return redirect()->route('leases.index')->with('status', 'Lease created and unit marked occupied.');
    }

    public function edit(Lease $lease)
    {
        $this->ensureVisible(Lease::class, $lease);

        return view('leases.form', ['lease' => $lease->load('unit.property', 'tenant')]);
    }

    public function update(Request $request, Lease $lease)
    {
        $this->ensureVisible(Lease::class, $lease);

        $data = $request->validate([
            'end_date' => ['nullable', 'date', 'after:' . $lease->start_date->format('Y-m-d')],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'ended'])],
        ]);
        $data['deposit'] = $data['deposit'] ?? 0;

        DB::transaction(function () use ($lease, $data) {
            $lease->update($data);
            $lease->unit->update(['status' => $lease->status === 'active' ? 'occupied' : 'vacant']);
        });

        return redirect()->route('leases.index')->with('status', 'Lease updated.');
    }

    public function destroy(Lease $lease)
    {
        $this->ensureVisible(Lease::class, $lease);

        DB::transaction(function () use ($lease) {
            if ($lease->status === 'active') {
                $lease->unit->update(['status' => 'vacant']);
            }
            $lease->delete();
        });

        return redirect()->route('leases.index')->with('status', 'Lease deleted.');
    }
}
