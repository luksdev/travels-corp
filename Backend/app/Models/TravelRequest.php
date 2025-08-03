<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRequest extends Model
{
    /** @use HasFactory<\Database\Factories\TravelRequestFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'destination',
        'departure_date',
        'return_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date'    => 'date',
        ];
    }

    /**
     * Get the user that owns the travel request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the request can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'requested';
    }

    /**
     * Check if the status can be changed
     */
    public function canChangeStatus(): bool
    {
        return $this->status === 'requested';
    }
}
