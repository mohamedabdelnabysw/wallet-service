<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Wallet;
use Illuminate\Support\Str;

class WalletFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check()
    {
        $response = $this->get('/api/health');
        $response->assertStatus(200)->assertJson(['status' => 'ok']);
    }

    public function test_complete_wallet_flow()
    {
        // 1. Create Wallet A
        $responseA = $this->postJson('/api/wallets', [
            'owner_name' => 'Alice',
            'currency' => 'USD',
        ]);
        $responseA->assertStatus(201);
        $walletAId = $responseA->json('id');

        // 2. Create Wallet B
        $responseB = $this->postJson('/api/wallets', [
            'owner_name' => 'Bob',
            'currency' => 'USD',
        ]);
        $responseB->assertStatus(201);
        $walletBId = $responseB->json('id');

        // 3. Deposit to A (Idempotent)
        $idempotencyKey = Str::uuid()->toString();
        $depositResponse = $this->postJson("/api/wallets/{$walletAId}/deposit", [
            'amount' => 1000,
        ], ['Idempotency-Key' => $idempotencyKey]);

        $depositResponse->assertStatus(200);
        $this->assertEquals(1000, $depositResponse->json('balance'));

        // 4. Repeat Deposit with SAME key -> Should not add funds
        $depositResponse2 = $this->postJson("/api/wallets/{$walletAId}/deposit", [
            'amount' => 1000,
        ], ['Idempotency-Key' => $idempotencyKey]);

        $depositResponse2->assertStatus(200);
        // The balance returned might be current balance, but let's check actual wallet balance
        $this->assertDatabaseHas('wallets', ['id' => $walletAId, 'balance' => 1000]);

        // 5. Transfer A -> B
        $transferResponse = $this->postJson('/api/transfers', [
            'source_wallet_id' => $walletAId,
            'target_wallet_id' => $walletBId,
            'amount' => 200,
        ]);
        $transferResponse->assertStatus(201);

        // 6. Verify Balances
        $this->assertDatabaseHas('wallets', ['id' => $walletAId, 'balance' => 800]);
        $this->assertDatabaseHas('wallets', ['id' => $walletBId, 'balance' => 200]);

        // 7. Withdraw Insufficient funds
        $withdrawFail = $this->postJson("/api/wallets/{$walletAId}/withdraw", [
            'amount' => 5000,
        ]);
        $withdrawFail->assertStatus(400); // Bad Request / Logic Error

        // 8. Withdraw Valid amount
        $withdrawResponse = $this->postJson("/api/wallets/{$walletAId}/withdraw", [
            'amount' => 100,
        ]);
        $withdrawResponse->assertStatus(200);
        $this->assertDatabaseHas('wallets', ['id' => $walletAId, 'balance' => 700]);

        // 9. Transaction History
        $historyResponse = $this->getJson("/api/wallets/{$walletAId}/transactions");
        $historyResponse->assertStatus(200);
        $history = $historyResponse->json('data');

        // Should have: Deposit (1000), Transfer (200), Withdraw (100) -> 3 transactions
        $this->assertCount(3, $history);
    }

    public function test_invalid_amount()
    {
        $responseA = $this->postJson('/api/wallets', [
            'owner_name' => 'Alice',
            'currency' => 'USD',
        ]);
        $walletAId = $responseA->json('id');

        // Test Deposit 0
        $response = $this->postJson("/api/wallets/{$walletAId}/deposit", [
            'amount' => 0,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test Deposit Negative
        $response = $this->postJson("/api/wallets/{$walletAId}/deposit", [
            'amount' => -100,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test Withdraw 0
        $response = $this->postJson("/api/wallets/{$walletAId}/withdraw", [
            'amount' => 0,
        ]);
        $response->assertStatus(422);

        // Test Transfer 0
        $responseB = $this->postJson('/api/wallets', [
            'owner_name' => 'Bob',
            'currency' => 'USD',
        ]);
        $walletBId = $responseB->json('id');

        $response = $this->postJson('/api/transfers', [
            'source_wallet_id' => $walletAId,
            'target_wallet_id' => $walletBId,
            'amount' => -50,
        ]);
        $response->assertStatus(422);
    }
}
