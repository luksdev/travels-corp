<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use App\Services\TravelRequestService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TravelRequestService();
    }

    /**
     * Test getFilteredQuery returns only user's requests for regular users
     */
    public function test_get_filtered_query_returns_only_users_requests_for_regular_users(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user->id]);
        TravelRequest::factory()->create(['user_id' => $user->id]);
        TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        $request = new Request();
        $query   = $this->service->getFilteredQuery($request, $user);

        $this->assertInstanceOf(Builder::class, $query);

        $results = $query->get();
        $this->assertCount(2, $results);

        foreach ($results as $travelRequest) {
            $this->assertEquals($user->id, $travelRequest->user_id);
        }
    }

    /**
     * Test getFilteredQuery returns all requests for admin users
     */
    public function test_get_filtered_query_returns_all_requests_for_admin_users(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user  = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $admin->id]);
        TravelRequest::factory()->create(['user_id' => $user->id]);

        $request = new Request();
        $query   = $this->service->getFilteredQuery($request, $admin);

        $results = $query->get();
        $this->assertCount(2, $results);
    }

    /**
     * Test applyFilters filters by status
     */
    public function test_apply_filters_filters_by_status(): void
    {
        $user = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user->id, 'status' => 'requested']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'status' => 'approved']);

        $request = new Request(['status' => 'approved']);
        $query   = $this->service->getFilteredQuery($request, $user);

        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('approved', $results->first()->status);
    }

    /**
     * Test applyFilters filters by destination
     */
    public function test_apply_filters_filters_by_destination(): void
    {
        $user = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'Tokyo, Japan']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'Paris, France']);

        $request = new Request(['destination' => 'Tokyo']);
        $query   = $this->service->getFilteredQuery($request, $user);

        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertStringContainsString('Tokyo', $results->first()->destination);
    }

    /**
     * Test applyFilters filters by departure date range
     */
    public function test_apply_filters_filters_by_departure_date_range(): void
    {
        $user = User::factory()->create();

        TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'departure_date' => '2025-01-15',
        ]);
        TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'departure_date' => '2025-02-15',
        ]);
        TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'departure_date' => '2025-03-15',
        ]);

        $request = new Request([
            'departure_from' => '2025-02-01',
            'departure_to'   => '2025-02-28',
        ]);

        $query   = $this->service->getFilteredQuery($request, $user);
        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('2025-02-15', $results->first()->departure_date->format('Y-m-d'));
    }

    /**
     * Test create method creates travel request with correct data
     */
    public function test_create_method_creates_travel_request_with_correct_data(): void
    {
        $user = User::factory()->create();
        $data = [
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01',
            'return_date'    => '2025-12-15',
        ];

        $travelRequest = $this->service->create($data, $user);

        $this->assertInstanceOf(TravelRequest::class, $travelRequest);
        $this->assertEquals($user->id, $travelRequest->user_id);
        $this->assertEquals('Tokyo, Japan', $travelRequest->destination);
        $this->assertEquals('2025-12-01', $travelRequest->departure_date->format('Y-m-d'));
        $this->assertEquals('2025-12-15', $travelRequest->return_date->format('Y-m-d'));
        $this->assertEquals('requested', $travelRequest->status);

        $this->assertDatabaseHas('travel_requests', [
            'user_id'        => $user->id,
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01 00:00:00',
            'return_date'    => '2025-12-15 00:00:00',
            'status'         => 'requested',
        ]);
    }

    /**
     * Test update method updates travel request
     */
    public function test_update_method_updates_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'destination'    => 'Updated Destination',
            'departure_date' => '2025-12-20',
        ];

        $updatedRequest = $this->service->update($travelRequest, $updateData);

        $this->assertEquals('Updated Destination', $updatedRequest->destination);
        $this->assertEquals('2025-12-20', $updatedRequest->departure_date->format('Y-m-d'));
    }

    /**
     * Test updateStatus method updates status and sends notification
     */
    public function test_update_status_method_updates_status_and_sends_notification(): void
    {
        Notification::fake();

        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $result = $this->service->updateStatus($travelRequest, 'approved');

        $this->assertTrue($result['success']);
        $this->assertEquals('Travel request approved successfully', $result['message']);
        $this->assertEquals('approved', $result['travelRequest']->status);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    /**
     * Test updateStatus method fails when request cannot change status
     */
    public function test_update_status_method_fails_when_request_cannot_change_status(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $result = $this->service->updateStatus($travelRequest, 'cancelled');

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot change status of this travel request', $result['message']);
    }

    /**
     * Test cancel method cancels travel request when allowed
     */
    public function test_cancel_method_cancels_travel_request_when_allowed(): void
    {
        Notification::fake();

        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $result = $this->service->cancel($travelRequest);

        $this->assertTrue($result['success']);
        $this->assertEquals('Travel request cancelled successfully', $result['message']);
        $this->assertEquals('cancelled', $result['travelRequest']->status);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    /**
     * Test cancel method fails when request cannot be cancelled
     */
    public function test_cancel_method_fails_when_request_cannot_be_cancelled(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $result = $this->service->cancel($travelRequest);

        $this->assertFalse($result['success']);
        $this->assertEquals('Cannot cancel this travel request. Only requested travel requests can be cancelled.', $result['message']);
    }

    /**
     * Test delete method deletes travel request
     */
    public function test_delete_method_deletes_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->service->delete($travelRequest);

        $this->assertSoftDeleted('travel_requests', [
            'id' => $travelRequest->id,
        ]);
    }

    /**
     * Test getPaginatedRequests returns paginated results
     */
    public function test_get_paginated_requests_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        TravelRequest::factory()->count(20)->create(['user_id' => $user->id]);

        $request           = new Request();
        $paginatedRequests = $this->service->getPaginatedRequests($request, $user, 10);

        $this->assertEquals(10, $paginatedRequests->perPage());
        $this->assertEquals(20, $paginatedRequests->total());
        $this->assertEquals(2, $paginatedRequests->lastPage());
    }

    /**
     * Test notification is sent with correct data
     */
    public function test_notification_is_sent_with_correct_data(): void
    {
        Notification::fake();

        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id'     => $user->id,
            'status'      => 'requested',
            'destination' => 'Tokyo, Japan',
        ]);

        $this->service->updateStatus($travelRequest, 'approved');

        Notification::assertSentTo($user, TravelRequestStatusChanged::class, function ($notification) use ($travelRequest) {
            return $notification->travelRequest->id === $travelRequest->id
                && $notification->oldStatus === 'requested'
                && $notification->newStatus === 'approved';
        });
    }
}
