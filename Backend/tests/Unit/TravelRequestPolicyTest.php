<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\TravelRequest;
use App\Models\User;
use App\Policies\TravelRequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TravelRequestPolicy();
    }

    /**
     * Test viewAny allows all authenticated users
     */
    public function test_view_any_allows_all_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    /**
     * Test view allows user to see their own travel request
     */
    public function test_view_allows_user_to_see_their_own_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $travelRequest));
    }

    /**
     * Test view prevents user from seeing other user's travel request
     */
    public function test_view_prevents_user_from_seeing_other_users_travel_request(): void
    {
        $user          = User::factory()->create();
        $otherUser     = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->view($user, $travelRequest));
    }

    /**
     * Test view allows admin to see any travel request
     */
    public function test_view_allows_admin_to_see_any_travel_request(): void
    {
        $admin         = User::factory()->create(['role' => UserRole::ADMIN]);
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($admin, $travelRequest));
    }

    /**
     * Test create allows all authenticated users
     */
    public function test_create_allows_all_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    /**
     * Test update allows user to update their own requested travel request
     */
    public function test_update_allows_user_to_update_their_own_requested_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $this->assertTrue($this->policy->update($user, $travelRequest));
    }

    /**
     * Test update prevents user from updating their own approved travel request
     */
    public function test_update_prevents_user_from_updating_their_own_approved_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $this->assertFalse($this->policy->update($user, $travelRequest));
    }

    /**
     * Test update prevents user from updating other user's travel request
     */
    public function test_update_prevents_user_from_updating_other_users_travel_request(): void
    {
        $user          = User::factory()->create();
        $otherUser     = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'requested',
        ]);

        $this->assertFalse($this->policy->update($user, $travelRequest));
    }

    /**
     * Test delete allows user to delete their own requested travel request
     */
    public function test_delete_allows_user_to_delete_their_own_requested_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $this->assertTrue($this->policy->delete($user, $travelRequest));
    }

    /**
     * Test delete prevents user from deleting their own approved travel request
     */
    public function test_delete_prevents_user_from_deleting_their_own_approved_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $this->assertFalse($this->policy->delete($user, $travelRequest));
    }

    /**
     * Test cancel allows user to cancel their own requested travel request
     */
    public function test_cancel_allows_user_to_cancel_their_own_requested_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'requested',
        ]);

        $this->assertTrue($this->policy->cancel($user, $travelRequest));
    }

    /**
     * Test cancel prevents user from cancelling their own approved travel request
     */
    public function test_cancel_prevents_user_from_cancelling_their_own_approved_travel_request(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status'  => 'approved',
        ]);

        $this->assertFalse($this->policy->cancel($user, $travelRequest));
    }

    /**
     * Test cancel prevents user from cancelling other user's travel request
     */
    public function test_cancel_prevents_user_from_cancelling_other_users_travel_request(): void
    {
        $user          = User::factory()->create();
        $otherUser     = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'requested',
        ]);

        $this->assertFalse($this->policy->cancel($user, $travelRequest));
    }

    /**
     * Test changeStatus allows only admins
     */
    public function test_change_status_allows_only_admins(): void
    {
        $admin         = User::factory()->create(['role' => UserRole::ADMIN]);
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->changeStatus($admin, $travelRequest));
        $this->assertFalse($this->policy->changeStatus($user, $travelRequest));
    }

    /**
     * Test restore allows only admins
     */
    public function test_restore_allows_only_admins(): void
    {
        $admin         = User::factory()->create(['role' => UserRole::ADMIN]);
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->restore($admin, $travelRequest));
        $this->assertFalse($this->policy->restore($user, $travelRequest));
    }

    /**
     * Test forceDelete allows only admins
     */
    public function test_force_delete_allows_only_admins(): void
    {
        $admin         = User::factory()->create(['role' => UserRole::ADMIN]);
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->forceDelete($admin, $travelRequest));
        $this->assertFalse($this->policy->forceDelete($user, $travelRequest));
    }
}
