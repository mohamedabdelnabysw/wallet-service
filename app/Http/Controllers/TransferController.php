<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class TransferController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function store(TransferRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            $transaction = $this->walletService->transfer(
                $validated['source_wallet_id'],
                $validated['target_wallet_id'],
                $validated['amount'],
                $idempotencyKey
            );
            return response()->json($transaction, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
