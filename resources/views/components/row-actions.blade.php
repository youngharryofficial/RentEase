@props(['edit' => null, 'delete' => null])
<div class="flex gap-3 justify-end text-sm">
    @if ($edit)
        <a href="{{ $edit }}" class="text-indigo-600 hover:underline">Edit</a>
    @endif
    @if ($delete)
        <form method="POST" action="{{ $delete }}" onsubmit="return confirm('Delete this record? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:underline">Delete</button>
        </form>
    @endif
</div>
