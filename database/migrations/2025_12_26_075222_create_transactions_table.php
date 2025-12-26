<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // deposit, withdraw, transfer
            $table->bigInteger('amount');
            $table->uuid('source_wallet_id')->nullable();
            $table->uuid('target_wallet_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->foreign('source_wallet_id')->references('id')->on('wallets');
            $table->foreign('target_wallet_id')->references('id')->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
