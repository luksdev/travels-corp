<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class TravelRequestStatusChangedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test notification via method returns correct channels
     */
    public function test_notification_via_method_returns_correct_channels(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $notification  = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');

        $channels = $notification->via($user);

        $this->assertEquals(['database', 'mail'], $channels);
    }

    /**
     * Test toMail method returns correct mail message for approved status
     */
    public function test_to_mail_method_returns_correct_mail_message_for_approved_status(): void
    {
        $user          = User::factory()->create(['name' => 'John Doe']);
        $travelRequest = TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01',
            'return_date'    => '2025-12-15',
        ]);

        $notification = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');
        $mailMessage  = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
        $this->assertEquals('Travel Request approved', $mailMessage->subject);
        $this->assertStringContainsString('Hello John Doe!', $mailMessage->greeting);
        $this->assertStringContainsString('Tokyo, Japan', $mailMessage->introLines[0]);
        $this->assertStringContainsString('approved', $mailMessage->introLines[0]);
        $this->assertStringContainsString('Dec 1, 2025', $mailMessage->introLines[1]);
        $this->assertStringContainsString('Dec 15, 2025', $mailMessage->introLines[2]);
        $this->assertStringContainsString('Thank you for using OnHappy!', $mailMessage->introLines[3]);
    }

    /**
     * Test toMail method returns correct mail message for cancelled status
     */
    public function test_to_mail_method_returns_correct_mail_message_for_cancelled_status(): void
    {
        $user          = User::factory()->create(['name' => 'Jane Doe']);
        $travelRequest = TravelRequest::factory()->create([
            'user_id'     => $user->id,
            'destination' => 'Paris, France',
        ]);

        $notification = new TravelRequestStatusChanged($travelRequest, 'requested', 'cancelled');
        $mailMessage  = $notification->toMail($user);

        $this->assertEquals('Travel Request cancelled', $mailMessage->subject);
        $this->assertStringContainsString('Hello Jane Doe!', $mailMessage->greeting);
        $this->assertStringContainsString('Paris, France', $mailMessage->introLines[0]);
        $this->assertStringContainsString('cancelled', $mailMessage->introLines[0]);
    }

    /**
     * Test toMail method handles travel request without return date
     */
    public function test_to_mail_method_handles_travel_request_without_return_date(): void
    {
        $user          = User::factory()->create(['name' => 'John Doe']);
        $travelRequest = TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01',
            'return_date'    => null,
        ]);

        $notification = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');
        $mailMessage  = $notification->toMail($user);

        $this->assertCount(3, $mailMessage->introLines);
        $this->assertStringContainsString('Dec 1, 2025', $mailMessage->introLines[1]);
        $this->assertStringContainsString('Thank you for using OnHappy!', $mailMessage->introLines[2]);
    }

    /**
     * Test toArray method returns correct array representation
     */
    public function test_to_array_method_returns_correct_array_representation(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01',
            'return_date'    => '2025-12-15',
        ]);

        $notification = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');
        $array        = $notification->toArray($user);

        $expectedArray = [
            'travel_request_id' => $travelRequest->id,
            'destination'       => 'Tokyo, Japan',
            'old_status'        => 'requested',
            'new_status'        => 'approved',
            'departure_date'    => $travelRequest->departure_date,
            'return_date'       => $travelRequest->return_date,
            'message'           => 'Your travel request to Tokyo, Japan has been approved.',
        ];

        $this->assertEquals($expectedArray, $array);
    }

    /**
     * Test toArray method handles travel request without return date
     */
    public function test_to_array_method_handles_travel_request_without_return_date(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id'        => $user->id,
            'destination'    => 'Tokyo, Japan',
            'departure_date' => '2025-12-01',
            'return_date'    => null,
        ]);

        $notification = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');
        $array        = $notification->toArray($user);

        $this->assertNull($array['return_date']);
        $this->assertEquals('Your travel request to Tokyo, Japan has been approved.', $array['message']);
    }

    /**
     * Test notification implements ShouldQueue interface
     */
    public function test_notification_implements_should_queue_interface(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $notification  = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }

    /**
     * Test notification uses Queueable trait
     */
    public function test_notification_uses_queueable_trait(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $notification  = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');

        $this->assertTrue(method_exists($notification, 'onQueue'));
        $this->assertTrue(method_exists($notification, 'onConnection'));
        $this->assertTrue(method_exists($notification, 'delay'));
    }

    /**
     * Test notification constructor sets properties correctly
     */
    public function test_notification_constructor_sets_properties_correctly(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $notification  = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');

        $this->assertEquals($travelRequest->id, $notification->travelRequest->id);
        $this->assertEquals('requested', $notification->oldStatus);
        $this->assertEquals('approved', $notification->newStatus);
    }

    /**
     * Test notification message for different status combinations
     */
    public function test_notification_message_for_different_status_combinations(): void
    {
        $user          = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id'     => $user->id,
            'destination' => 'Tokyo, Japan',
        ]);

        $approvedNotification = new TravelRequestStatusChanged($travelRequest, 'requested', 'approved');
        $approvedArray        = $approvedNotification->toArray($user);
        $this->assertStringContainsString('approved', $approvedArray['message']);

        $cancelledNotification = new TravelRequestStatusChanged($travelRequest, 'requested', 'cancelled');
        $cancelledArray        = $cancelledNotification->toArray($user);
        $this->assertStringContainsString('cancelled', $cancelledArray['message']);
    }
}
