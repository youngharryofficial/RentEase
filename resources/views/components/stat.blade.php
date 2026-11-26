@props(['label', 'value', 'icon' => 'chart', 'color' => 'indigo', 'hint' => null])
@php
    $tones = [
        'indigo' => 'bg-indigo-100 text-indigo-600',
        'green' => 'bg-green-100 text-green-600',
        'amber' => 'bg-amber-100 text-amber-600',
        'red' => 'bg-red-100 text-red-600',
        'sky' => 'bg-sky-100 text-sky-600',
    ];
@endphp
<div class="bg-white shadow-sm sm:rounded-lg p-5 flex items-center gap-4">
    <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center {{ $tones[$color] ?? $tones['indigo'] }}">
        <x-icon :name="$icon" class="w-6 h-6" />
    </div>
    <div class="min-w-0">
        <p class="text-sm text-gray-500">{{ $label }}</p>
        <p class="text-xl font-semibold text-gray-900 truncate">{{ $value }}</p>
        @if ($hint)
            <p class="text-xs text-gray-400">{{ $hint }}</p>
        @endif
    </div>
</div>
