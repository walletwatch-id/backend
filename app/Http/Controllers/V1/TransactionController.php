<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Transaction::class, 'transaction');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $transactions = QueryBuilder::for(Transaction::class)
            ->allowedIncludes([
                'user',
                'paylater',
            ]);

        if ($request->user->role === 'ADMIN') {
            $transactions = $transactions
                ->allowedFilters([
                    'user_id',
                    'paylater_id',
                ])
                ->allowedSorts([
                    'user_id',
                    'paylater_id',
                ]);
        } else {
            $transactions = $transactions
                ->allowedFilters([
                    'paylater_id',
                ])
                ->allowedSorts([
                    'paylater_id',
                ])
                ->where('user_id', $request->user->id);
        }

        $transactions = $transactions->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('transactions', $transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = new Transaction($request->validated());

        if ($request->user->role !== 'ADMIN') {
            $transaction->fill([
                'user_id' => $request->user->id,
            ]);
        }

        $transaction->save();

        return ResponseFormatter::singleton('transaction', $transaction, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        $transaction = QueryBuilder::for(Transaction::where('id', $request->transaction))
            ->allowedIncludes([
                'user',
                'paylater',
            ])->firstOrFail();

        return ResponseFormatter::singleton('transaction', $transaction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $transaction->fill($request->validated());
        $transaction->save();

        return ResponseFormatter::singleton('transaction', $transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return ResponseFormatter::singleton('transaction', $transaction);
    }
}
