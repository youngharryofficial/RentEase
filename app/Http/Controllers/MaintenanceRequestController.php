<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksVisibility;
use App\Models\MaintenanceRequest;
use App\Notifications\MaintenanceRequestReceived;
use App\Notifications\MaintenanceStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MaintenanceRequestController extends Controller
{
    use ChecksVisibility;

    public function index(Request $request)
    {
        $requests = MaintenanceRequest::visibleTo($request->user())
            ->with('unit.property', 'tenant')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($request->q, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('title', 'like', "%{$s}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('name', 'like', "%{$s}%"))
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('maintenance.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $lease = $request->user()->leases()->where('status', 'active')->with('unit.property')->first();

        if (! $lease) {
            return redirect()->route('dashboard')->with('error', 'You need an active lease to send a maintenance request.');
        }

        return view('maintenance.create', compact('lease'));
    }

    public function store(Request $request)
    {
        $lease = $request->user()->leases()->where('status', 'active')->with('unit.property.landlord')->first();

        abort_unless($lease, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $maintenance = MaintenanceRequest::create($data + [
            'unit_id' => $lease->unit_id,
            'tenant_id' => $request->user()->id,
            'status' => 'open',
        ]);

        try {
            $lease->unit->property->landlord->notify(new MaintenanceRequestReceived($maintenance));
        } catch (\Throwable $e) {
            Log::error('Maintenance email failed: ' . $e->getMessage());
        }

        return redirect()->route('maintenance.index')->with('status', 'Request sent to your landlord.');
    }

    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $this->ensureVisible(MaintenanceRequest::class, $maintenance);

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
        ]);

        $changed = $maintenance->status !== $data['status'];
        $maintenance->update($data);

        if ($changed) {
            try {
                $maintenance->tenant->notify(new MaintenanceStatusUpdated($maintenance));
            } catch (\Throwable $e) {
                Log::error('Maintenance status email failed: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Request updated.');
    }

    public function destroy(MaintenanceRequest $maintenance)
    {
        $this->ensureVisible(MaintenanceRequest::class, $maintenance);
        $maintenance->delete();

        return back()->with('status', 'Request deleted.');
    }
}
