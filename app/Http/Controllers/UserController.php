<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = $request->user()->isAdmin();

        $users = User::query()
            ->when(! $isAdmin, fn ($q) => $q->where('role', 'tenant'))
            ->when($isAdmin && $request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->q, fn ($query, $s) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
            ))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = $request->validate(['password' => ['required', 'string', 'min:8']])['password'];

        $user = User::create($data);

        try {
            $user->notify(new WelcomeNotification());
        } catch (\Throwable $e) {
            Log::error('Welcome email failed: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, User $user)
    {
        $this->guardTarget($request, $user);

        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->guardTarget($request, $user);

        $data = $this->validated($request, $user);

        if ($request->filled('password')) {
            $data['password'] = $request->validate(['password' => ['string', 'min:8']])['password'];
        }

        // Nobody can change their own role (prevents locking yourself out)
        if ($user->id === $request->user()->id) {
            unset($data['role']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->guardTarget($request, $user);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account here.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $isAdmin = $request->user()->isAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'phone' => ['nullable', 'regex:/^(\+?254|0)[17]\d{8}$/'],
            'role' => [$isAdmin ? 'required' : 'nullable', Rule::in(['admin', 'landlord', 'tenant'])],
        ], [
            'phone.regex' => 'Enter a valid Safaricom number like 0712345678 (no spaces).',
        ]);

        if (! $isAdmin) {
            $data['role'] = 'tenant';
        }

        if (! empty($data['phone'])) {
            $digits = preg_replace('/\D/', '', $data['phone']);
            $data['phone'] = str_starts_with($digits, '0') ? '254' . substr($digits, 1) : $digits;
        }

        return $data;
    }

    /** Landlords may only manage tenants. */
    private function guardTarget(Request $request, User $user): void
    {
        abort_if(! $request->user()->isAdmin() && $user->role !== 'tenant', 403);
    }
}
