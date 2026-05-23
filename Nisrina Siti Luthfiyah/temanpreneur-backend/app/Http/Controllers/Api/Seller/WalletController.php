<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get wallet info and recent transactions
     * GET /api/seller/wallet
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $wallet = $user->wallet ?? Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
            ]);

            // Get recent transactions
            $transactions = $wallet->transactions()
                ->with('order')
                ->latest()
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => (float) $wallet->balance,
                    'total_earned' => (float) $wallet->total_earned,
                    'total_withdrawn' => (float) $wallet->total_withdrawn,
                    'transactions' => $transactions->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'type' => $transaction->type,
                            'amount' => (float) $transaction->amount,
                            'description' => $transaction->description,
                            'created_at' => $transaction->created_at,
                            'order_id' => $transaction->order_id,
                        ];
                    }),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data wallet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request withdrawal (placeholder - implement later)
     * POST /api/seller/wallet/withdraw
     */
    public function withdraw(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:50000',
            ]);

            $user = $request->user();
            $wallet = $user->wallet;

            if (!$wallet || $wallet->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak cukup untuk penarikan'
                ], 400);
            }

            // For now, just return success message
            // In real implementation, this would create a withdrawal request
            return response()->json([
                'success' => true,
                'message' => 'Permintaan penarikan akan diproses dalam 1-3 hari kerja'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
