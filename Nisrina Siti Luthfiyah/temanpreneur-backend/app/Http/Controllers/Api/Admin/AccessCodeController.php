<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAccessCodeRequest;
use App\Models\AccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessCodeController extends Controller
{
    /**
     * Get all access codes
     * GET /api/admin/access-codes
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->query();
            $query = AccessCode::with('createdBy', 'usedBy')->latest();

            if ($request->get('is_used')) {
                $query->where('is_used', $request->get('is_used') === 'true');
            }

            $accessCodes = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $accessCodes->items(),
                'pagination' => [
                    'total' => $accessCodes->total(),
                    'per_page' => $accessCodes->perPage(),
                    'current_page' => $accessCodes->currentPage(),
                    'last_page' => $accessCodes->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching access codes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar kode akses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single access code
     * GET /api/admin/access-codes/{id}
     */
    public function show(string $id)
    {
        try {
            $accessCode = AccessCode::with('createdBy', 'usedBy')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $accessCode
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching access code: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kode akses tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new access codes
     * POST /api/admin/access-codes
     */
    public function store(CreateAccessCodeRequest $request)
    {
        try {
            // Create access codes
            for ($i = 0; $i < ($request->quantity ?? 1); $i++) {
                AccessCode::create([
                    'code' => $request->code . ($i > 0 ? "-{$i}" : ''),
                    'quantity' => 1,
                    'is_used' => false,
                    'used_by' => null,
                    'used_at' => null,
                    'expires_at' => $request->expires_at,
                    'description' => $request->description,
                    'created_by' => auth()->id(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Kode akses berhasil dibuat ({$request->quantity} buah)",
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating access codes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kode akses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete access code
     * DELETE /api/admin/access-codes/{id}
     */
    public function destroy(string $id)
    {
        try {
            $accessCode = AccessCode::findOrFail($id);

            if ($accessCode->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus kode akses yang sudah digunakan',
                ], 422);
            }

            $accessCode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kode akses berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting access code: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kode akses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if access code is valid
     * GET /api/admin/access-codes/check/{code}
     */
    public function check(string $code)
    {
        try {
            $accessCode = AccessCode::where('code', $code)->first();

            if (!$accessCode) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Kode akses tidak ditemukan',
                ], 404);
            }

            $isValid = $accessCode->isValid();

            return response()->json([
                'success' => true,
                'valid' => $isValid,
                'accessCode' => [
                    'id' => $accessCode->id,
                    'code' => $accessCode->code,
                    'is_used' => $accessCode->is_used,
                    'expires_at' => $accessCode->expires_at,
                ],
                'message' => $isValid ? 'Kode akses valid' : ($accessCode->is_used ? 'Kode akses sudah digunakan' : 'Kode akses sudah kadaluarsa'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error checking access code: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa kode akses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
