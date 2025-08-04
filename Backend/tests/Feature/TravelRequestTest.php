<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can create travel request
     */
    public function test_user_can_create_travel_request(): void
    {
        $user = User::factory()->create();

        $data = [
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01',
            'return_date'    => '2025-12-15',
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/travel-requests', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'user',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'user_id'        => $user->id,
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01 00:00:00',
            'return_date'    => '2025-12-15 00:00:00',
            'status'         => 'requested',
        ]);
    }

    /**
     * Test user can list their own travel requests
     */
    public function test_user_can_list_own_travel_requests(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'Paris']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'London']);
        TravelRequest::factory()->create(['user_id' => $otherUser->id, 'destination' => 'Rome']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $destinations = collect($response->json('data'))->pluck('destination');
        $this->assertTrue($destinations->contains('Paris'));
        $this->assertTrue($destinations->contains('London'));
        $this->assertFalse($destinations->contains('Rome'));
    }

    /**
     * Test admin can list all travel requests
     */
    public function test_admin_can_list_all_travel_requests(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user  = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $admin->id, 'destination' => 'Paris']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'London']);

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/travel-requests');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test user can view their own travel request
     */
    public function test_user_can_view_own_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'user',
                ],
            ]);
    }

    /**
     * Test user cannot view other user's travel request
     */
    public function test_user_cannot_view_other_users_travel_request(): void
    {
        $user          = User::factory()->create();
        $otherUser     = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertForbidden();
    }

    /**
     * Test user can update their own travel request when status is requested
     */
    public function test_user_can_update_own_travel_request_when_requested(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $updateData = [
            'destination'    => 'Updated Destination',
            'departure_date' => '2025-12-20',
            'return_date'    => '2025-12-25',
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        $response->assertOk();

        $this->assertDatabaseHas('travel_requests', [
            'id'             => $travelRequest->id,
            'destination'    => 'Updated Destination',
            'departure_date' => '2025-12-20 00:00:00',
        ]);
    }

    /**
     * Test user cannot update travel request when status is approved
     */
    public function test_user_cannot_update_travel_request_when_approved(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $updateData = ['destination' => 'Updated Destination'];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        $response->assertForbidden();
    }

    /**
     * Test admin can update travel request status
     */
    public function test_admin_can_update_travel_request_status(): void
    {
        Notification::fake();

        $admin         = User::factory()->create(['role' => UserRole::ADMIN]);
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $response = $this->actingAs($admin, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Travel request approved successfully',
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id'     => $travelRequest->id,
            'status' => 'approved',
        ]);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    /**
     * Test regular user cannot update travel request status
     */
    public function test_regular_user_cannot_update_travel_request_status(): void
    {
        $user          = User::factory()->create();
        $otherUser     = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'requested',
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user can cancel their own travel request when status is requested
     */
    public function test_user_can_cancel_own_travel_request_when_requested(): void
    {
        Notification::fake();

        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertOk()
            ->assertJson([
                'message' => 'Travel request cancelled successfully',
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id'     => $travelRequest->id,
            'status' => 'cancelled',
        ]);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    /**
     * Test user cannot cancel travel request when status is approved
     */
    public function test_user_cannot_cancel_travel_request_when_approved(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/travel-requests/{$travelRequest->id}/cancel");

        $response->assertForbidden();
    }

    /**
     * Test travel requests can be filtered by status
     */
    public function test_travel_requests_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user->id, 'status' => 'requested']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'status' => 'approved']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'status' => 'cancelled']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests?status=approved');

        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals('approved', $response->json('data.0.status'));
    }

    /**
     * Test travel requests can be filtered by destination
     */
    public function test_travel_requests_can_be_filtered_by_destination(): void
    {
        $user = User::factory()->create();

        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'Tokyo, Japan']);
        TravelRequest::factory()->create(['user_id' => $user->id, 'destination' => 'Paris, France']);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests?destination=Tokyo');

        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertStringContainsString('Tokyo', $response->json('data.0.destination'));
    }

    /**
     * Test travel requests can be filtered by departure date range
     */
    public function test_travel_requests_can_be_filtered_by_departure_date_range(): void
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

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/travel-requests?departure_from=2025-02-01&departure_to=2025-02-28');

        $response->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals('2025-02-15T00:00:00.000000Z', $response->json('data.0.departure_date'));
    }

    /**
     * Test unauthenticated user cannot access travel requests
     */
    public function test_unauthenticated_user_cannot_access_travel_requests(): void
    {
        $response = $this->getJson('/api/travel-requests');
        $response->assertUnauthorized();
    }

    /**
     * Test user can delete their own travel request when status is requested
     */
    public function test_user_can_delete_own_travel_request_when_requested(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Travel request deleted successfully',
            ]);

        $this->assertSoftDeleted('travel_requests', [
            'id' => $travelRequest->id,
        ]);
    }

    /**
     * Test validation errors for invalid travel request data
     */
    public function test_validation_errors_for_invalid_travel_request_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/travel-requests', [
                'destination'    => '',
                'departure_date' => 'invalid-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'departure_date']);
    }
}
