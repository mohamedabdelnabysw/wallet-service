<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_all_wallets()
    {
        Wallet::factory()->count(3)->create();

        $response = $this->getJson('/api/wallets');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_index_filters_wallets_by_currency()
    {
        Wallet::factory()->create(['currency' => 'USD']);
        Wallet::factory()->create(['currency' => 'EUR']);
        Wallet::factory()->create(['currency' => 'USD']);

        $response = $this->getJson('/api/wallets?currency=USD');

        $response->assertStatus(200)
            ->assertJsonCount(2); // Should verify they are USD
    }

    public function test_show_returns_correct_wallet()
    {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $wallet->id,
                'owner_name' => $wallet->owner_name,
                'currency' => $wallet->currency,
            ]);
    }

    public function test_show_returns_404_for_non_existent_wallet()
    {
        $response = $this->getJson("/api/wallets/non-existent-id");

        $response->assertStatus(404);
    }

    public function test_transactions_lists_wallet_transactions()
    {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->count(5)->create([
            'source_wallet_id' => $wallet->id,
        ]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_transactions_filters_by_type()
    {
        $wallet = Wallet::factory()->create();

        // Create 2 DEPOSIT
        Transaction::factory()->count(2)->create([
            'target_wallet_id' => $wallet->id,
            'type' => TransactionType::DEPOSIT,
        ]);

        // Create 1 WITHDRAW
        Transaction::factory()->count(1)->create([
            'source_wallet_id' => $wallet->id,
            'type' => TransactionType::WITHDRAW,
        ]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions?type=deposit");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
