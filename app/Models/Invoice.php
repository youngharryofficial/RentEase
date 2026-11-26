<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['lease_id', 'period', 'amount', 'balance', 'due_date', 'status'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin()
            ? $query
            : $query->whereHas('lease.unit.property', fn ($q) => $q->where('landlord_id', $user->id));
    }

    /** Recompute balance and status from the amount and successful payments. */
    public function recalculate(): void
    {
        $paid = (float) $this->payments()->where('status', 'success')->sum('amount');

        $this->balance = max(0, (float) $this->amount - $paid);
        $this->status = $this->balance <= 0
            ? 'paid'
            : ($paid > 0 ? 'partial' : ($this->due_date->lt(today()) ? 'overdue' : 'unpaid'));
        $this->save();
    }
}
