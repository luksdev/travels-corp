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
        // Users can view their own requests, admins can view all
        return true;
    }

    /**
     * View specific travel request
     */
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        // Users can view their own requests, admins can view all
        return $user->isAdmin() || $travelRequest->user_id === $user->id;
    }

    /**
     * Create travel request
     */
    public function create(User $user): bool
    {
        // All authenticated users can create travel requests
        return true;
    }

    /**
     * Update travel request
     */
    public function update(User $user, TravelRequest $travelRequest): bool
    {
        // Users can only update their own requests if status is 'requested'
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
     * Cancel travel request after approval
     */
    public function cancel(User $user, TravelRequest $travelRequest): bool
    {
        // Users cannot cancel approved requests, only requested ones
        return $travelRequest->user_id === $user->id && $travelRequest->status === 'requested';
    }

    /**
     * Change status (approve/reject)
     */
    public function changeStatus(User $user, TravelRequest $travelRequest): bool
    {
        // Only admins can change status
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
