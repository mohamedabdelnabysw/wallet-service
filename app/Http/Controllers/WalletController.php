<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\ListWalletsRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Requests\TransactionHistoryRequest;
use App\Filters\WalletFilter;
use App\Filters\TransactionFilter;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function store(StoreWalletRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $wallet = $this->walletService->createWallet($validated['owner_name'], $validated['currency']);

        return response()->json($wallet, 201);
    }

    public function show(string $id): JsonResponse
    {
        $wallet = $this->walletService->getWallet($id);
        return response()->json($wallet);
    }

    public function index(ListWalletsRequest $request, WalletFilter $filter): JsonResponse
    {
        $wallets = $this->walletService->listWallets($filter);
        return response()->json($wallets);
    }

    public function deposit(DepositRequest $request, string $id): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            $transaction = $this->walletService->deposit(
                $id,
                $request->validated('amount'),
                $idempotencyKey
            );
            return response()->json(['balance' => $transaction->targetWallet->balance, 'transaction_id' => $transaction->id], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function withdraw(WithdrawRequest $request, string $id): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            $transaction = $this->walletService->withdraw(
                $id,
                $request->validated('amount'),
                $idempotencyKey
            );
            return response()->json(['balance' => $transaction->sourceWallet->balance, 'transaction_id' => $transaction->id], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function balance(string $id): JsonResponse
    {
        $wallet = $this->walletService->getWallet($id);
        return response()->json(['balance' => $wallet->balance]);
    }

    public function transactions(TransactionHistoryRequest $request, string $id, TransactionFilter $filter): JsonResponse
    {
        // Ensure wallet exists
        $this->walletService->getWallet($id);

        $transactions = $this->walletService->getTransactions($id, $filter);
        return response()->json($transactions);
    }
}
