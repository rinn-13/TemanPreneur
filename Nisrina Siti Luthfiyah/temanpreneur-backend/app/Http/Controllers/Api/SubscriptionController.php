<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Business;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Upgrade seller to premium (kode akses dari admin atau alur pembayaran eksternal).
     * POST /api/subscription/upgrade
     */
    public function upgrade(Request $request)
    {
        try {
            $request->validate([
                'plan' => 'nullable|string|in:premium',
                'duration' => 'nullable|string',
                'access_code' => 'nullable|string|max:64',
            ]);

            $user = $request->user();

            $business = Business::where('user_id', $user->id)->first();

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $business
            ]);

            if (!in_array($business->status, ['approved', 'active'], true) || !$business->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko Anda belum disetujui. Premium hanya untuk penjual terverifikasi.',
                ], 422);
            }

            if ($business->is_premium || $user->role === 'seller_premium') {
                return response()->json([
                    'success' => true,
                    'message' => 'Akun Anda sudah premium.',
                    'data' => ['is_premium' => true],
                ], 200);
            }

            $code = $request->input('access_code');
            if ($code) {
                return $this->redeemPremiumCode($user, $business, $code);
            }

            // Tanpa gateway pembayaran: arahkan ke admin / kode akses
            return response()->json([
                'success' => false,
                'message' => 'Untuk mengaktifkan premium, hubungi admin sekolah untuk kode akses atau gunakan parameter access_code jika sudah memiliki kode.',
                'payment_url' => null,
                'hint' => 'Masukkan kode premium dari admin (halaman Upgrade) lalu coba lagi.',
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Subscription upgrade error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses upgrade',
            ], 500);
        }
    }

    protected function redeemPremiumCode($user, Business $business, string $code)
    {
        $accessCode = AccessCode::where('code', trim($code))->first();

        if (!$accessCode || !$accessCode->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode akses tidak valid atau sudah digunakan.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $accessCode->update([
                'is_used' => true,
                'used_by' => $user->id,
                'used_at' => now(),
            ]);

            $business->update(['is_premium' => true]);
            $user->update(['role' => 'seller_premium']);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'premium_activated',
                'title' => 'Premium Aktif',
                'message' => 'Selamat! Paket premium Anda telah diaktifkan.',
                'is_read' => false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Premium berhasil diaktifkan!',
                'payment_url' => null,
                'data' => [
                    'is_premium' => true,
                    'role' => 'seller_premium',
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
