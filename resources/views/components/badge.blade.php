@props(['value'])
@php
    $colors = [
        'paid' => 'green', 'success' => 'green', 'resolved' => 'green', 'active' => 'green', 'occupied' => 'green',
        'partial' => 'yellow', 'pending' => 'yellow', 'in_progress' => 'yellow', 'unpaid' => 'yellow', 'medium' => 'yellow',
        'overdue' => 'red', 'failed' => 'red', 'high' => 'red', 'open' => 'red',
    ];
    $map = [
        'green' => 'bg-green-100 text-green-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'red' => 'bg-red-100 text-red-800',
        'gray' => 'bg-gray-100 text-gray-700',
    ];
    $cls = $map[$colors[$value] ?? 'gray'];
@endphp
<span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">{{ ucfirst(str_replace('_', ' ', $value)) }}</span>
