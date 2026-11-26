<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksVisibility;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    use ChecksVisibility;

    public function index(Request $request)
    {
        $properties = Property::visibleTo($request->user())
            ->with('landlord')
            ->withCount('units')
            ->when($request->q, fn ($query, $s) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('location', 'like', "%{$s}%")
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('properties.index', compact('properties'));
    }

    public function create(Request $request)
    {
        return view('properties.form', [
            'property' => new Property(),
            'landlords' => $this->landlords($request),
        ]);
    }

    public function store(Request $request)
    {
        Property::create($this->validated($request));

        return redirect()->route('properties.index')->with('status', 'Property added.');
    }

    public function edit(Request $request, Property $property)
    {
        $this->ensureVisible(Property::class, $property);

        return view('properties.form', [
            'property' => $property,
            'landlords' => $this->landlords($request),
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $this->ensureVisible(Property::class, $property);
        $property->update($this->validated($request));

        return redirect()->route('properties.index')->with('status', 'Property updated.');
    }

    public function destroy(Property $property)
    {
        $this->ensureVisible(Property::class, $property);
        $property->delete();

        return redirect()->route('properties.index')->with('status', 'Property deleted.');
    }

    private function validated(Request $request): array
    {
        $isAdmin = $request->user()->isAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'landlord_id' => [
                $isAdmin ? 'required' : 'nullable',
                Rule::exists('users', 'id')->where('role', 'landlord'),
            ],
        ]);

        if (! $isAdmin) {
            $data['landlord_id'] = $request->user()->id;
        }

        return $data;
    }

    private function landlords(Request $request)
    {
        return $request->user()->isAdmin()
            ? User::where('role', 'landlord')->orderBy('name')->get()
            : collect();
    }
}
