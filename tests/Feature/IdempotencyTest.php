<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_key_returns_cached_response()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['balance' => 100]);
        $key = 'test_key_123';

        $response1 = $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 50,
        ], ['Idempotency-Key' => $key]);

        $response1->assertStatus(200);
        $this->assertEquals(150, $wallet->fresh()->balance);

        // Second request with same key
        $response2 = $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 50,
        ], ['Idempotency-Key' => $key]);

        $response2->assertStatus(200);

        // Balance should NOT increase again
        $this->assertEquals(150, $wallet->fresh()->balance);

        // Content should be same
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    public function test_different_keys_execute_twice()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['balance' => 100]);

        $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 50,
        ], ['Idempotency-Key' => 'key_1'])->assertStatus(200);

        $this->assertEquals(150, $wallet->fresh()->balance);

        $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 50,
        ], ['Idempotency-Key' => 'key_2'])->assertStatus(200);

        $this->assertEquals(200, $wallet->fresh()->balance);
    }

    public function test_concurrent_requests_are_locked()
    {
        // This is hard to test perfectly in a sync test runner without separate processes, 
        // but we can mock the lock to simulate a locked state.

        Cache::shouldReceive('has')->andReturn(false);
        Cache::shouldReceive('lock')->andReturn($lock = \Mockery::mock(\Illuminate\Contracts\Cache\Lock::class));
        $lock->shouldReceive('get')->andReturn(false); // Lock failed to acquire

        $user = User::factory()->create();
        $wallet = Wallet::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 50,
        ], ['Idempotency-Key' => 'concurrent_key']);

        $response->assertStatus(409);
    }

    public function test_missing_idempotency_key_returns_error()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['balance' => 100]);

        $response = $this->actingAs($user)->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 50,
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Idempotency-Key header is required']);
    }
}
