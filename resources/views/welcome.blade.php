<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#4f46e5">
        <title>RentEase - Rental &amp; Property Management</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800 bg-white">

        <header class="absolute inset-x-0 top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 text-white text-xl font-bold">
                    <x-icon name="building" class="w-7 h-7" /> RentEase
                </a>
                <nav class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-white font-medium hover:text-indigo-100">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">Get started</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <section style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 60%, #0f172a 100%);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-36 pb-24 text-center">
                <p class="inline-block px-3 py-1 rounded-full bg-white/10 text-indigo-100 text-xs font-medium tracking-wide">Built for landlords and tenants in Kenya</p>
                <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-white leading-tight">Rent collection,<br class="hidden sm:block"> without the chasing.</h1>
                <p class="mt-5 max-w-2xl mx-auto text-lg text-indigo-100">
                    Manage properties, tenants and leases in one place. Tenants pay rent by M-Pesa in seconds, and everyone is notified automatically.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">Go to dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-3 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-3 rounded-md border border-white/40 text-white font-semibold hover:bg-white/10">Create an account</a>
                        @endif
                    @endauth
                </div>
            </div>
        </section>

        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-3xl font-bold text-slate-900">Everything you need to run your rentals</h2>
                <p class="mt-3 text-slate-500">From the first invoice to the last receipt, RentEase keeps the whole rent cycle in one system.</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['banknotes', 'M-Pesa payments', 'Tenants pay rent with an STK Push prompt on their phone. Payments are confirmed automatically and receipts are recorded.'],
                    ['clock', 'Automatic invoicing', 'Generate a month of rent invoices for every active lease in one click, with due dates and balances tracked for you.'],
                    ['chart', 'Live dashboards', 'Landlords and admins see occupancy, income and arrears at a glance, with charts that update as payments arrive.'],
                    ['envelope', 'Email notifications', 'Welcome messages, payment receipts, rent reminders and maintenance updates are sent without lifting a finger.'],
                    ['wrench', 'Maintenance requests', 'Tenants report problems from their phone, and landlords track each request from open to resolved.'],
                    ['shield', 'Secure by design', 'Role-based access keeps every landlord, tenant and admin to their own data, with hashed passwords and validated forms.'],
                ] as [$icon, $title, $text])
                    <div class="rounded-xl border border-slate-200 p-6 bg-white hover:shadow-md transition">
                        <div class="w-12 h-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <x-icon :name="$icon" class="w-6 h-6" />
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-slate-50 border-y border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <h2 class="text-2xl font-bold text-slate-900 text-center">One system, three roles</h2>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ([
                        ['Admin', 'Oversees the whole platform: users, roles, all payments and system-wide analytics.'],
                        ['Landlord', 'Manages properties, units, tenants and leases, generates invoices and downloads reports.'],
                        ['Tenant', 'Views the lease, pays rent by M-Pesa, downloads receipts and reports maintenance issues.'],
                    ] as [$role, $text])
                        <div class="rounded-xl bg-white border border-slate-200 p-6">
                            <h3 class="font-semibold text-indigo-700">{{ $role }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <footer class="bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm text-slate-500 flex flex-wrap justify-between gap-2">
                <span>&copy; {{ date('Y') }} RentEase</span>
                <span>Built with Laravel</span>
            </div>
        </footer>
    </body>
</html>
