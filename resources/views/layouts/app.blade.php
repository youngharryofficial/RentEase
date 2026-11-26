<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#4f46e5">

        <title>{{ config('app.name', 'RentEase') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            table { border-collapse: collapse; }
            table thead tr { background: #f8fafc; }
            table thead th { font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 600; color: #64748b; }
            table tbody tr:hover { background: #f8fafc; }
            .bg-white.shadow-sm { border: 1px solid #e5e7eb; }
            .sm\:rounded-lg { border-radius: .75rem; }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-xs text-slate-500 flex flex-wrap justify-between gap-2">
                    <span>&copy; {{ date('Y') }} RentEase</span>
                    <span>Rental &amp; Property Management System</span>
                </div>
            </footer>
        </div>
    </body>
</html>
