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
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8"
             style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 60%, #0f172a 100%);">
            <a href="/" class="flex items-center gap-2 text-white text-3xl font-bold">
                <x-icon name="building" class="w-9 h-9" />
                RentEase
            </a>
            <p class="text-indigo-200 text-sm mt-1">Rental &amp; property management</p>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white shadow-xl overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
