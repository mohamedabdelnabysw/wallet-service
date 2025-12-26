<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(array_column(TransactionType::cases(), 'value')),
            'amount' => fake()->numberBetween(10, 1000),
            'source_wallet_id' => Wallet::factory(),
            'target_wallet_id' => Wallet::factory(),
            'idempotency_key' => Str::random(32),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
