<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Filters\TransactionFilter;
use App\Filters\WalletFilter;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_can_be_filtered_by_currency()
    {
        Wallet::factory()->create(['currency' => 'USD']);
        Wallet::factory()->create(['currency' => 'EUR']);

        $request = new Request(['currency' => 'USD']);
        $filter = new WalletFilter($request);

        $wallets = Wallet::filter($filter)->get();

        $this->assertCount(1, $wallets);
        $this->assertEquals('USD', $wallets->first()->currency);
    }

    public function test_transaction_can_be_filtered_by_type()
    {
        Transaction::factory()->create(['type' => TransactionType::DEPOSIT]);
        Transaction::factory()->create(['type' => TransactionType::WITHDRAW]);

        $request = new Request(['type' => TransactionType::DEPOSIT->value]);
        $filter = new TransactionFilter($request);

        $transactions = Transaction::filter($filter)->get();

        $this->assertCount(1, $transactions);
        $this->assertEquals(TransactionType::DEPOSIT->value, $transactions->first()->type);
    }

    public function test_transaction_can_be_filtered_by_date_range()
    {
        $oldTransaction = Transaction::factory()->create(['created_at' => now()->subDays(10)]);
        $recentTransaction = Transaction::factory()->create(['created_at' => now()->subDays(1)]);

        $request = new Request([
            'date_from' => now()->subDays(5)->toDateTimeString(),
            'date_to' => now()->toDateTimeString(),
        ]);
        $filter = new TransactionFilter($request);

        $transactions = Transaction::filter($filter)->get();

        $this->assertCount(1, $transactions);
        $this->assertEquals($recentTransaction->id, $transactions->first()->id);
    }
}
