<?php

namespace App\Http\Controllers\Concerns;

trait ChecksVisibility
{
    /** Stop landlords from touching records that belong to someone else. */
    protected function ensureVisible(string $model, $record): void
    {
        abort_unless(
            $model::visibleTo(auth()->user())->whereKey($record->getKey())->exists(),
            403
        );
    }
}
