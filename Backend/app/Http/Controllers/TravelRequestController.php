<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Http\Requests\TravelRequest\UpdateStatusRequest;
use App\Http\Requests\TravelRequest\UpdateTravelRequestRequest;
use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Travel Request Controller
 */
class TravelRequestController extends Controller
{
    public function __construct(
        private readonly TravelRequestService $travelRequestService
    ) {
    }

    /**
     * List travel requests
     * @throws AuthorizationException
     */
    public function index(Request $request): TravelRequestCollection
    {
        $this->authorize('viewAny', TravelRequest::class);

        $travelRequests = $this->travelRequestService->getPaginatedRequests($request, $request->user());

        return new TravelRequestCollection($travelRequests);
    }

    /**
     * Create travel request
     */
    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        $travelRequest = $this->travelRequestService->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'data'    => new TravelRequestResource($travelRequest),
            'message' => 'Travel request created successfully',
        ], Response::HTTP_CREATED);
    }

    /**
     * Show travel request
     * @throws AuthorizationException
     */
    public function show(TravelRequest $travelRequest): TravelRequestResource
    {
        $this->authorize('view', $travelRequest);

        $travelRequest->load('user');

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Update travel request
     */
    public function update(UpdateTravelRequestRequest $request, TravelRequest $travelRequest): TravelRequestResource
    {
        $updatedTravelRequest = $this->travelRequestService->update(
            $travelRequest,
            $request->validated()
        );

        return new TravelRequestResource($updatedTravelRequest);
    }

    /**
     * Update travel request status
     * @throws AuthorizationException
     */
    public function updateStatus(UpdateStatusRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('changeStatus', $travelRequest);

        $result = $this->travelRequestService->updateStatus($travelRequest, $request->status);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'data'    => new TravelRequestResource($result['travelRequest']),
            'message' => $result['message'],
        ]);
    }

    /**
     * Cancel travel request
     * @throws AuthorizationException
     */
    public function cancel(TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('cancel', $travelRequest);

        $result = $this->travelRequestService->cancel($travelRequest);

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'data'    => new TravelRequestResource($result['travelRequest']),
            'message' => $result['message'],
        ]);
    }

    /**
     * Get travel requests statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->travelRequestService->getStats($request->user());

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Delete travel request
     * @throws AuthorizationException
     */
    public function destroy(TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('delete', $travelRequest);

        $this->travelRequestService->delete($travelRequest);

        return response()->json([
            'message' => 'Travel request deleted successfully',
        ]);
    }
}
