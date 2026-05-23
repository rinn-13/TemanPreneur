<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * Fetch all businesses for verification with status filtering
     * GET /api/admin/verifications?status=pending|approved|rejected|all&page=1&per_page=10
     */
    public function index(Request $request)
    {
        try {
            $status = $request->get('status', 'all'); // Default to all statuses
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            $query = Business::with('user');

            // Filter by status if specified
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $businesses = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => BusinessResource::collection($businesses->items()),
                'pagination' => [
                    'current_page' => $businesses->currentPage(),
                    'per_page' => $businesses->perPage(),
                    'total' => $businesses->total(),
                    'last_page' => $businesses->lastPage(),
                ],
                'counts' => [
                    'all' => Business::count(),
                    'pending' => Business::where('status', 'pending')->count(),
                    'approved' => Business::where('status', 'approved')->count(),
                    'rejected' => Business::where('status', 'rejected')->count(),
                    'active' => Business::whereIn('status', ['active', 'approved'])->count(),
                    'blocked' => Business::where('status', 'blocked')->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching businesses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch businesses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single business pending verification
     * GET /api/admin/verifications/{id}
     * 
     * Includes products only if explicitly requested via query parameter
     */
    public function show(string $id, Request $request)
    {
        try {
            $includeProducts = $request->get('include_products') === 'true';
            
            $query = Business::where('id', $id)
                ->where('status', 'pending')
                ->with('user');
            
            // Only load products if explicitly requested
            if ($includeProducts) {
                $query->with('products', 'blogs');
            }
            
            $business = $query->first();

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business verification record not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new BusinessResource($business)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching business verification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Business verification record not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update business verification status (Approve or Reject)
     * PUT/PATCH /api/admin/verifications/{id}
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:verified,rejected',
                'rejection_reason' => 'nullable|string|max:500',
                'type' => 'nullable|in:regular,premium', // Determine seller type
            ]);

            $business = Business::findOrFail($id);

            // Validate business is in pending status
            if ($business->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya toko dengan status pending yang dapat diverivikasi/ditolak'
                ], 422);
            }

            $user = $business->user;

            if ($request->status === 'verified') {
                // Approve the business
                $business->update([
                    'status' => 'approved',
                    'is_verified' => true,
                    'is_premium' => $request->type === 'premium' ? true : false,
                    'processed_at' => now(),
                    'rejection_reason' => null,
                ]);

                // Add seller role to user if not already present
                if (!$user->hasRole('seller')) {
                    $roles = $user->roles ?? [$user->role];
                    if (!in_array('seller', $roles)) {
                        $roles[] = 'seller';
                    }
                    $user->update(['roles' => $roles]);
                }

                // Create notification for seller
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Toko Diverifikasi! 🎉',
                    'message' => "Toko '{$business->name}' Anda telah disetujui oleh admin. Anda sekarang bisa mulai menjual produk!",
                    'type' => 'business_verified',
                    'related_id' => $business->id,
                    'is_read' => false,
                ]);

                Log::info("Business {$business->id} ({$business->name}) verified as " . ($request->type ?? 'regular') . " seller");
            } else {
                // Reject the business
                $business->update([
                    'status' => 'rejected',
                    'is_verified' => false,
                    'processed_at' => now(),
                    'rejection_reason' => $request->rejection_reason,
                ]);

                // Create notification for seller
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Pengajuan Toko Ditolak',
                    'message' => "Pengajuan toko '{$business->name}' Anda ditolak. Alasan: {$request->rejection_reason}",
                    'type' => 'business_rejected',
                    'related_id' => $business->id,
                    'is_read' => false,
                ]);

                Log::info("Business {$business->id} ({$business->name}) rejected. Reason: {$request->rejection_reason}");
            }

            return response()->json([
                'success' => true,
                'message' => $request->status === 'verified' 
                    ? "Toko {$business->name} berhasil diverifikasi!"
                    : "Toko {$business->name} ditolak.",
                'data' => new BusinessResource($business->fresh('user', 'products'))
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating business verification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status verifikasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store (Not used for verification workflow)
     */
    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Use PUT/PATCH instead to update verification status'
        ], 405);
    }

    /**
     * Destroy (Delete rejected business)
     * DELETE /api/admin/verifications/{id}
     */
    public function destroy(string $id)
    {
        try {
            $business = Business::findOrFail($id);

            // Only allow deletion of rejected businesses
            if ($business->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengajuan yang ditolak yang dapat dihapus'
                ], 422);
            }

            $businessName = $business->name;
            $business->delete();

            Log::info("Rejected business {$id} ({$businessName}) deleted by admin");

            return response()->json([
                'success' => true,
                'message' => "Pengajuan toko '{$businessName}' berhasil dihapus"
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting business: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengajuan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

