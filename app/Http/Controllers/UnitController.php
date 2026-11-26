<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksVisibility;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    use ChecksVisibility;

    public function index(Request $request)
    {
        $user = $request->user();

        $units = Unit::visibleTo($user)
            ->with('property')
            ->when($request->q, fn ($query, $s) => $query->where(
                fn ($w) => $w->where('unit_number', 'like', "%{$s}%")->orWhere('type', 'like', "%{$s}%")
            ))
            ->when($request->property_id, fn ($query, $id) => $query->where('property_id', $id))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('property_id')
            ->orderBy('unit_number')
            ->paginate(10)
            ->withQueryString();

        return view('units.index', [
            'units' => $units,
            'properties' => Property::visibleTo($user)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('units.form', [
            'unit' => new Unit(),
            'properties' => Property::visibleTo($request->user())->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Unit::create($this->validated($request));

        return redirect()->route('units.index')->with('status', 'Unit added.');
    }

    public function edit(Request $request, Unit $unit)
    {
        $this->ensureVisible(Unit::class, $unit);

        return view('units.form', [
            'unit' => $unit,
            'properties' => Property::visibleTo($request->user())->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Unit $unit)
    {
        $this->ensureVisible(Unit::class, $unit);
        $unit->update($this->validated($request, $unit));

        return redirect()->route('units.index')->with('status', 'Unit updated.');
    }

    public function destroy(Unit $unit)
    {
        $this->ensureVisible(Unit::class, $unit);
        $unit->delete();

        return redirect()->route('units.index')->with('status', 'Unit deleted.');
    }

    private function validated(Request $request, ?Unit $unit = null): array
    {
        $data = $request->validate([
            'property_id' => ['required', Rule::exists('properties', 'id')],
            'unit_number' => [
                'required', 'string', 'max:50',
                Rule::unique('units')->where('property_id', $request->property_id)->ignore($unit?->id),
            ],
            'type' => ['required', 'string', 'max:50'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
        ]);

        abort_unless(
            Property::visibleTo($request->user())->whereKey($data['property_id'])->exists(),
            403
        );

        return $data;
    }
}
