<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Models\TravelRequest;
use App\Models\User;

class TravelRequestPolicy
{
    /**
     * View any travel requests
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * View specific travel request
     */
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin() || $travelRequest->user_id === $user->id;
    }

    /**
     * Create travel request
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Update travel request
     */
    public function update(User $user, TravelRequest $travelRequest): bool
    {
        return $travelRequest->user_id === $user->id && $travelRequest->status === 'requested';
    }

    /**
     * Delete travel request
     */
    public function delete(User $user, TravelRequest $travelRequest): bool
    {
        // Users can only delete their own requests if status is 'requested'
        return $travelRequest->user_id === $user->id && $travelRequest->status === 'requested';
    }

    /**
     * Cancel travel request
     */
    public function cancel(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin() && $travelRequest->status === 'requested';
    }

    /**
     * Change status (approve/reject)
     */
    public function changeStatus(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Restore travel request
     */
    public function restore(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Force delete travel request
     */
    public function forceDelete(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }
}
