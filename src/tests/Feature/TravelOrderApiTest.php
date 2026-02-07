<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TravelOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    public function test_user_can_create_and_list_only_own_orders(): void
    {
        $userA = User::factory()->create(['role' => 'USER']);
        $userB = User::factory()->create(['role' => 'USER']);

        $tokenA = $this->tokenFor($userA);

        // create A
        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->postJson('/api/travel-orders', [
                'requester_name' => 'Alline',
                'destination' => 'Paris',
                'departure_date' => '2026-03-01',
                'return_date' => '2026-03-10',
            ])->assertCreated();

        // create B directly
        TravelOrder::factory()->create(['user_id' => $userB->id]);

        // list A -> only 1
        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->getJson('/api/travel-orders')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_update_status_admin_only(): void
    {
        $user = User::factory()->create(['role' => 'USER']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id, 'status' => 'REQUESTED']);

        $token = $this->tokenFor($user);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'APPROVED'
            ])->assertForbidden();
    }

    public function test_admin_can_approve_and_sends_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user = User::factory()->create(['role' => 'USER']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id, 'status' => 'REQUESTED']);

        $token = $this->tokenFor($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'APPROVED'
            ])->assertOk()
              ->assertJsonPath('data.status', 'APPROVED');

        Notification::assertSentTo($user, \App\Notifications\TravelOrderStatusChanged::class);
    }

    public function test_admin_cannot_cancel_if_already_approved(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user = User::factory()->create(['role' => 'USER']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id, 'status' => 'APPROVED']);

        $token = $this->tokenFor($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", [
                'status' => 'CANCELED'
            ])->assertStatus(422);
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $userA = User::factory()->create(['role' => 'USER']);
        $userB = User::factory()->create(['role' => 'USER']);
        $orderB = TravelOrder::factory()->create(['user_id' => $userB->id]);

        $tokenA = $this->tokenFor($userA);

        $this->withHeader('Authorization', "Bearer {$tokenA}")
            ->getJson("/api/travel-orders/{$orderB->id}")
            ->assertForbidden();
    }

    public function test_admin_can_view_any_order(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user = User::factory()->create(['role' => 'USER']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $token = $this->tokenFor($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/travel-orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_admin_can_list_all_orders(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $userA = User::factory()->create(['role' => 'USER']);
        $userB = User::factory()->create(['role' => 'USER']);

        TravelOrder::factory()->create(['user_id' => $userA->id]);
        TravelOrder::factory()->create(['user_id' => $userB->id]);

        $token = $this->tokenFor($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/travel-orders')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_index_can_filter_by_status(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        TravelOrder::factory()->create(['status' => 'REQUESTED']);
        TravelOrder::factory()->create(['status' => 'APPROVED']);
        TravelOrder::factory()->create(['status' => 'APPROVED']);

        $token = $this->tokenFor($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/travel-orders?status=APPROVED')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_store_sets_status_requested_and_associates_user(): void
    {
        $user = User::factory()->create(['role' => 'USER']);
        $token = $this->tokenFor($user);

        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/travel-orders', [
                'requester_name' => 'Alline',
                'destination' => 'Rome',
                'departure_date' => '2026-04-01',
                'return_date' => '2026-04-05',
            ])->assertCreated();

        $this->assertDatabaseHas('travel_orders', [
            'user_id' => $user->id,
            'destination' => 'Rome',
            'status' => 'REQUESTED',
        ]);

        $res->assertJsonPath('data.status', 'REQUESTED');
    }
    
    public function test_admin_can_cancel_if_not_approved_and_sends_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'ADMIN']);
        $user  = User::factory()->create(['role' => 'USER']);
        $order = TravelOrder::factory()->create(['user_id' => $user->id, 'status' => 'REQUESTED']);

        $token = $this->tokenFor($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/travel-orders/{$order->id}/status", ['status' => 'CANCELED'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELED');

        Notification::assertSentTo($user, \App\Notifications\TravelOrderStatusChanged::class);
    }
}