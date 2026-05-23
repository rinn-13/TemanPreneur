<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Http\Requests\CreateBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Http\Resources\BusinessResource;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Utils\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BusinessController extends Controller
{
    /**
     * Submit business application (by seller)
     * POST /api/businesses
     * 
     * Only authenticated users can submit
     * User must not already have a business
     */
    public function store(CreateBusinessRequest $request)
    {
        try {
            $user = $request->user();

            // Double-check user doesn't already have business
            if ($user->business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki satu toko. Tidak dapat membuat lebih dari satu.',
                ], 400);
            }

            // Create business with pending status
            $business = Business::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 'pending',      // Waiting for admin approval
                'is_verified' => false,
                'is_premium' => false,
            ]);

            // Create notification for admin
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Pengajuan Toko Baru',
                    'message' => "{$user->name} telah mengajukan toko: {$business->name}",
                    'type' => 'business_verification',
                    'related_id' => $business->id,
                    'is_read' => false,
                ]);
            }

            Log::info("Business application submitted by user {$user->id}: {$business->name}");

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan toko berhasil dikirim! Admin akan meninjau dalam 1-2 hari kerja.',
                'data' => new BusinessResource($business->load('user'))
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Business store error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengajuan toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user's business info
     * GET /api/businesses
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $query = Business::query();

            // Admin can filter by status and limit results
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('limit')) {
                $query->limit((int) $request->limit);
            }

            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $businesses = $query->with('user', 'products')->get();

            return response()->json([
                'success' => true,
                'data' => BusinessResource::collection($businesses)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Business index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data bisnis'
            ], 500);
        }
    }

    /**
     * Public list of verified stores (catalog / home)
     * GET /api/businesses/all
     */
    public function all(Request $request)
    {
        try {
            $query = Business::with('user')
                ->where('is_verified', true)
                ->whereIn('status', ['approved', 'active']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('limit')) {
                $query->limit(max(1, min((int) $request->limit, 100)));
            }

            $businesses = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => BusinessResource::collection($businesses),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Business all error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar toko',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get public business info (catalog view)
     * GET /api/businesses/{id}
     */
    public function publicShow($businessId)
    {
        try {
            $business = Business::with('user', 'products', 'blogs')
                ->where(function ($query) use ($businessId) {
                    $query->where('id', $businessId)
                          ->orWhere('user_id', $businessId);
                })
                ->where('is_verified', true)
                ->whereIn('status', ['approved', 'active'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => new BusinessResource($business)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Public business show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Toko tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get authenticated user's business info
     * GET /api/businesses/{business}
     */
    public function show(Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak terautentikasi'
                ], 401);
            }

            // Only owner or admin can view
            if ($user->id !== $business->user_id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak diizinkan mengakses toko ini'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => new BusinessResource($business->load('user', 'products'))
            ], 200);
        } catch (\Exception $e) {
            Log::error('Business show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User is not authenticated'], 401);
            }

            $business = $user->business;
            if (!$business) {
                return response()->json(['error' => 'No business found', 'message' => 'User does not have a registered business'], 404);
            }

            $productsCount = $business->products()->count();
            $blogCount = $business->blogs()->count();
            $salesCount = $business->products()->sum('total_sold');
            $ordersCount = OrderItem::whereIn('product_id', $business->products()->pluck('id'))->count();
            $revenue = OrderItem::whereIn('product_id', $business->products()->pluck('id'))->sum('subtotal');

            return response()->json([
                'business' => $business->load('user'),
                'stats' => [
                    'products' => $productsCount,
                    'blogs' => $blogCount,
                    'sales' => $salesCount,
                    'orders' => $ordersCount,
                    'revenue' => $revenue,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Business dashboard error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to load dashboard', 'message' => 'Unable to load dashboard data'], 500);
        }
    }

    public function products(Request $request)
    {
        try {
            $business = auth()->user()->business;
            if (!$business) {
                return response()->json(['error' => 'No business found', 'message' => 'User does not have a registered business'], 404);
            }

            $products = $business->products()->get();
            return response()->json(['data' => $products], 200);
        } catch (\Exception $e) {
            Log::error('Business products error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to load products', 'message' => 'Unable to retrieve product list'], 500);
        }
    }

    public function settings(Request $request)
    {
        try {
            $business = auth()->user()->business()->with('user', 'products')->first();
            if (!$business) {
                return response()->json([
                    'message' => 'Toko tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'data' => new BusinessResource($business),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Business settings error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data toko',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $business = $request->user()->business()->with('user', 'products')->first();
            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new BusinessResource($business),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Business me error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data toko',
            ], 500);
        }
    }

    public function settingsProducts(Request $request)
    {
        try {
            $business = auth()->user()->business;
            if (!$business) {
                return response()->json(['error' => 'No business found', 'message' => 'User does not have a registered business'], 404);
            }

            $products = $business->products()->get();
            return response()->json(['data' => $products], 200);
        } catch (\Exception $e) {
            Log::error('Business settings products error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to load products', 'message' => 'Unable to retrieve business products'], 500);
        }
    }

    public function settingsBlogs(Request $request)
    {
        try {
            $business = auth()->user()->business;
            if (!$business) {
                return response()->json(['error' => 'No business found', 'message' => 'User does not have a registered business'], 404);
            }

            $blogs = $business->blogs()->get();
            return response()->json(['data' => $blogs], 200);
        } catch (\Exception $e) {
            Log::error('Business settings blogs error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to load blogs', 'message' => 'Unable to retrieve business blogs'], 500);
        }
    }

    /**
     * Update business info (by owner only)
     * PUT/PATCH /api/businesses/{business}
     */
    public function update(UpdateBusinessRequest $request, string $business)
    {
        try {
            $user = $request->user();
            $businessModel = Business::find($business);

            if (!$businessModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan',
                ], 404);
            }

            // Only owner can update
            if ($user->id !== $businessModel->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak diizinkan mengubah toko ini'
                ], 403);
            }

            // Only can update if status is not rejected
            if ($businessModel->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko ditolak. Hubungi admin untuk mengajukan ulang.'
                ], 400);
            }

            $validated = [
                'name' => $request->name ?? $businessModel->name,
                'description' => $request->description ?? $businessModel->description,
                'category' => $request->category ?? $businessModel->category,
                'address' => $request->address ?? $businessModel->address,
                'phone' => $request->phone ?? $businessModel->phone,
                'theme_color' => $request->theme_color ?? $businessModel->theme_color,
            ];

            // Handle file uploads
            if ($request->hasFile('logo')) {
                if ($businessModel->logo) {
                    \Storage::disk('public')->delete($businessModel->logo);
                }
                $validated['logo'] = $request->file('logo')->store('businesses/logos', 'public');
            }

            if ($request->hasFile('banner')) {
                if ($businessModel->banner) {
                    \Storage::disk('public')->delete($businessModel->banner);
                }
                $validated['banner'] = $request->file('banner')->store('businesses/banners', 'public');
            }

            $businessModel->update($validated);

            Log::info("Business {$businessModel->id} updated by user {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Data toko berhasil diperbarui',
                'data' => new BusinessResource($businessModel->fresh('user', 'products'))
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Business update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verify(Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User is not authenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Admin access required'], 403);
            }

            $business->update(['is_verified' => true]);

            Notification::create([
                'user_id' => $business->user_id,
                'type' => 'business_verified',
                'title' => 'Usaha Terverifikasi',
                'message' => 'Usaha Anda telah diverifikasi oleh admin. Sekarang Anda dapat mengupload produk.',
            ]);

            return response()->json($business, 200);
        } catch (\Exception $e) {
            Log::error('Business verify error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to verify business', 'message' => 'Unable to verify business'], 500);
        }
    }

    public function upgrade(Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User is not authenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Admin access required'], 403);
            }

            $business->update(['is_premium' => true]);
            $business->user->update(['role' => 'seller_premium']);

            Notification::create([
                'user_id' => $business->user_id,
                'type' => 'premium_activated',
                'title' => 'Premium Aktif',
                'message' => 'Selamat! Usaha Anda sekarang menjadi premium.',
            ]);

            return response()->json($business, 200);
        } catch (\Exception $e) {
            Log::error('Business upgrade error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to upgrade business', 'message' => 'Unable to upgrade business'], 500);
        }
    }

    public function approve(Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User is not authenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Admin access required'], 403);
            }

            $business->update(['status' => 'approved', 'is_verified' => true, 'processed_at' => now()]);

            $userModel = $business->user;
            $roles = $userModel->roles ?? [$userModel->role];
            if (!in_array('seller', $roles)) {
                $roles[] = 'seller';
                $userModel->roles = $roles;
            }
            $userModel->role = 'seller';
            $userModel->save();

            Notification::create([
                'user_id' => $business->user_id,
                'type' => 'business_approved',
                'title' => 'Usaha Disetujui ✅',
                'message' => 'Selamat! Usaha "' . $business->name . '" Anda telah disetujui oleh admin. Anda sekarang dapat menambahkan produk dan melayani pembeli.',
            ]);

            return response()->json(['message' => 'Business approved successfully', 'data' => $business], 200);
        } catch (\Exception $e) {
            Log::error('Business approve error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to approve business', 'message' => 'Unable to approve business'], 500);
        }
    }

    public function reject(Request $request, Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User is not authenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Admin access required'], 403);
            }

            $reason = $request->input('reason') ?? 'Tidak ada alasan yang diberikan';

            $business->update(['status' => 'rejected', 'processed_at' => now(), 'rejection_reason' => $reason]);

            Notification::create([
                'user_id' => $business->user_id,
                'type' => 'business_rejected',
                'title' => 'Usaha Ditolak ❌',
                'message' => 'Mohon maaf, usaha "' . $business->name . '" Anda ditolak dengan alasan: ' . $reason,
            ]);

            return response()->json(['message' => 'Business rejected successfully', 'data' => $business], 200);
        } catch (\Exception $e) {
            Log::error('Business reject error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to reject business', 'message' => 'Unable to reject business'], 500);
        }
    }

    /**
     * Block a business/store (admin only)
     */
    public function block(Request $request, Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Admin access required'], 403);
            }

            if ($business->status === 'blocked') {
                return response()->json(['error' => 'Business already blocked'], 400);
            }

            $reason = $request->input('reason') ?? 'Tidak ada alasan';

            $business->update([
                'status' => 'blocked', 
                'processed_at' => now(), 
                'rejection_reason' => $reason
            ]);

            Notification::create([
                'user_id' => $business->user_id,
                'type' => 'business_blocked',
                'title' => 'Toko Diblokir 🚫',
                'message' => 'Toko "' . $business->name . '" Anda telah diblokir: ' . $reason,
            ]);

            return response()->json(['message' => 'Business blocked', 'data' => $business], 200);
        } catch (\Exception $e) {
            \Log::error('Business block error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to block'], 500);
        }
    }

    /**
     * Unblock a business/store (admin only)
     */
    public function unblock(Business $business)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            if ($business->status !== 'blocked') {
                return response()->json(['error' => 'Business not blocked'], 400);
            }

            $business->update([
                'status' => 'active', 
                'processed_at' => now(), 
                'rejection_reason' => null
            ]);

            Notification::create([
                'user_id' => $business->user_id,
                'type' => 'business_unblocked',
                'title' => 'Toko Diaktifkan ✅',
                'message' => 'Toko "' . $business->name . '" Anda telah diaktifkan kembali.',
            ]);

            return response()->json(['message' => 'Business unblocked', 'data' => $business], 200);
        } catch (\Exception $e) {
            \Log::error('Business unblock error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to unblock'], 500);
        }
    }

    /**
     * Upload business logo
     * POST /api/businesses/{id}/logo
     */
    public function uploadLogo(Request $request, Business $business)
    {
        try {
            // Validate ownership
            if ($business->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Validate file
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
            ]);

            // Delete old logo if exists
            if ($business->logo && \Storage::disk('public')->exists($business->logo)) {
                \Storage::disk('public')->delete($business->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('businesses/logos', 'public');
            $business->update(['logo' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Logo berhasil diupload',
                'data' => [
                    'logo' => $business->logo,
                    'logo_url' => ImageUrl::normalize($business->logo)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logo upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload business banner
     * POST /api/businesses/{id}/banner
     */
    public function uploadBanner(Request $request, Business $business)
    {
        try {
            // Validate ownership
            if ($business->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Validate file
            $request->validate([
                'banner' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
            ]);

            // Delete old banner if exists
            if ($business->banner && \Storage::disk('public')->exists($business->banner)) {
                \Storage::disk('public')->delete($business->banner);
            }

            // Store new banner
            $path = $request->file('banner')->store('businesses/banners', 'public');
            $business->update(['banner' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil diupload',
                'data' => [
                    'banner' => $business->banner,
                    'banner_url' => ImageUrl::normalize($business->banner)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Banner upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
