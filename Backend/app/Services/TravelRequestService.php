<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Travel Request Service
 */
class TravelRequestService
{
    /**
     * Get filtered travel requests query
     */
    public function getFilteredQuery(Request $request, User $user): Builder
    {
        $query = TravelRequest::with('user');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $this->applyFilters($query, $request);

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by destination
        if ($request->filled('destination')) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
        }

        // Filter by departure date range
        if ($request->filled('departure_from')) {
            $query->where('departure_date', '>=', $request->departure_from);
        }

        if ($request->filled('departure_to')) {
            $query->where('departure_date', '<=', $request->departure_to);
        }

        // Filter by created date range
        if ($request->filled('created_from')) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->where('created_at', '<=', $request->created_to);
        }
    }

    /**
     * Create a new travel request
     */
    public function create(array $data, User $user): TravelRequest
    {
        $travelRequest = TravelRequest::create([
            'user_id'        => $user->id,
            'destination'    => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date'    => $data['return_date'] ?? null,
            'status'         => 'requested',
        ]);

        $travelRequest->load('user');

        return $travelRequest;
    }

    /**
     * Update a travel request
     */
    public function update(TravelRequest $travelRequest, array $data): TravelRequest
    {
        $travelRequest->update($data);
        $travelRequest->load('user');

        return $travelRequest;
    }

    /**
     * Update travel request status
     */
    public function updateStatus(TravelRequest $travelRequest, string $newStatus): array
    {
        if (! $travelRequest->canChangeStatus()) {
            return [
                'success' => false,
                'message' => 'Cannot change status of this travel request',
            ];
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->update(['status' => $newStatus]);

        $this->sendStatusChangeNotification($travelRequest, $oldStatus, $newStatus);

        return [
            'success'       => true,
            'travelRequest' => $travelRequest->load('user'),
            'message'       => "Travel request {$newStatus} successfully",
        ];
    }

    /**
     * Cancel a travel request
     */
    public function cancel(TravelRequest $travelRequest): array
    {
        if (! $travelRequest->canBeCancelled()) {
            return [
                'success' => false,
                'message' => 'Cannot cancel this travel request. Only requested travel requests can be cancelled.',
            ];
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->update(['status' => 'cancelled']);

        $this->sendStatusChangeNotification($travelRequest, $oldStatus, 'cancelled');

        return [
            'success'       => true,
            'travelRequest' => $travelRequest->load('user'),
            'message'       => 'Travel request cancelled successfully',
        ];
    }

    /**
     * Delete a travel request
     */
    public function delete(TravelRequest $travelRequest): void
    {
        $travelRequest->delete();
    }

    /**
     * Send status change notification
     */
    private function sendStatusChangeNotification(
        TravelRequest $travelRequest,
        string $oldStatus,
        string $newStatus
    ): void {
        $travelRequest->user->notify(new TravelRequestStatusChanged(
            $travelRequest,
            $oldStatus,
            $newStatus
        ));
    }

    /**
     * Get paginated travel requests
     */
    public function getPaginatedRequests(Request $request, User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getFilteredQuery($request, $user)->paginate($perPage);
    }

    /**
     * Get travel requests statistics
     */
    public function getStats(User $user): array
    {
        $query = TravelRequest::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return [
            'total'     => $query->count(),
            'requested' => (clone $query)->where('status', 'requested')->count(),
            'approved'  => (clone $query)->where('status', 'approved')->count(),
            'cancelled'  => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }
}
