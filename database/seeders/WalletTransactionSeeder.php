<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Wallet::factory(5)->create()->each(function (Wallet $wallet) {
            Transaction::factory(50)->create([
                'source_wallet_id' => $wallet->id,
                'type' => fake()->randomElement([
                    TransactionType::DEPOSIT->value,
                    TransactionType::WITHDRAW->value,
                ]),
            ]);
        });
    }
}
