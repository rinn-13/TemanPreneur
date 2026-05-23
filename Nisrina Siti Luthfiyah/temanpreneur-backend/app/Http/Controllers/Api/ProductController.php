<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Utils\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Get product catalog (public, paginated with filtering)
     * GET /api/products
     */
    public function index(Request $request)
    {
        try {
            // Show products only if business is APPROVED and product is ACTIVE
            // This ensures data sync: products hidden until usaha/bisnis disetujui
            $query = Product::with(['business.user', 'category'])
                ->whereHas('business', function ($q) {
                    $q->where('status', 'approved'); // Only approved businesses
                })
                ->where('status', 'active') // Only active products
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');

            // Filtering
            if ($request->business_id) {
                $query->where('business_id', $request->business_id);
            }

            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            } elseif ($request->category) {
                $category = strtolower($request->category);
                $query->where(function ($q) use ($category) {
                    $q->whereRaw('LOWER(category) = ?', [$category])
                      ->orWhereHas('category', function ($cq) use ($category) {
                          $cq->whereRaw('LOWER(slug) = ?', [$category])
                             ->orWhereRaw('LOWER(name) = ?', [$category]);
                      });
                });
            }

            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            // Price filtering
            if ($request->min_price) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->max_price) {
                $query->where('price', '<=', $request->max_price);
            }

            // Sorting
            $sort = $request->sort ?? 'terbaru';
            match($sort) {
                'terbaru' => $query->orderBy('created_at', 'desc'),
                'terlaris' => $query->orderBy('total_sold', 'desc'),
                'termurah' => $query->orderBy('price', 'asc'),
                'termahal' => $query->orderBy('price', 'desc'),
                'rating' => $query->orderBy('reviews_avg_rating', 'desc'),
                'random' => $query->inRandomOrder(),
                default => $query->orderBy('created_at', 'desc'),
            };

            $perPage = min(max((int) ($request->per_page ?? 12), 1), 20);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: list all products (monitoring only, no verification filters)
     * GET /api/admin/products
     */
    public function adminIndex(Request $request)
    {
        try {
            $perPage = min(max((int) ($request->per_page ?? 50), 1), 200);
            $query = Product::with(['business.user', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews');

            $status = $request->input('status', 'all');
            if ($status === 'removed') {
                $query->where('status', 'removed');
            } elseif ($status === 'active' || $status === 'blocked') {
                $query->where('status', $status);
            } else {
                $query->where('status', '!=', 'removed');
            }

            if ($request->filled('category')) {
                $category = $request->category;
                $query->where(function ($q) use ($category) {
                    $q->where('category', $category)
                        ->orWhereHas('category', function ($cq) use ($category) {
                            $cq->where('slug', $category);
                        });
                });
            }

            if ($request->filled('business_id')) {
                $query->where('business_id', (int) $request->business_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('business', function ($bq) use ($search) {
                            $bq->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $sort = $request->input('sort', 'newest');
            if ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching admin products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil produk (admin)',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product details
     * GET /api/products/{id}
     */
    public function show(Request $request, string $product)
    {
        try {
            $productModel = Product::with(['business.user', 'category', 'reviews.order.buyer'])
                ->find($product);

            if (!$productModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            $productModel->loadAvg('reviews', 'rating');

            return response()->json([
                'success' => true,
                'data' => new ProductResource($productModel)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Admin: soft-remove a product by setting status = 'removed'
     * DELETE /api/admin/products/{id}
     */
    public function adminDelete($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update(['status' => 'removed']);
            Log::info("Admin removed product {$product->id}");
            return response()->json([
                'success' => true,
                'message' => 'Produk telah ditandai sebagai dihapus'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error admin deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new product (seller only)
     * POST /api/products
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $user = $request->user();
            $business = $user->business;

            // Ensure user has a business, but allow product creation even if business not verified
            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada toko terdaftar untuk pengguna ini',
                ], 404);
            }

            // Check product limit for regular sellers (only count active products)
            if (!$business->is_premium) {
                $existingProducts = $business->products()->where('status', 'active')->count();
                if ($existingProducts >= 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Seller reguler hanya dapat menjual maksimal 2 produk. Upgrade ke seller premium untuk menjual lebih banyak produk.',
                        'limit' => 2,
                        'current_count' => $existingProducts
                    ], 400);
                }
            }

            // Create product
            $data = $request->validated();
            $data['business_id'] = $business->id;

            if (!empty($data['category_id'])) {
                $category = Category::find($data['category_id']);
                if ($category) {
                    $data['category'] = $category->slug ?: Str::slug($category->name);
                }
            }

            // Handle image uploads (multiple images support)
            $uploadedImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $uploadedImages[] = $imageFile->store('products', 'public');
                }
            } elseif ($request->hasFile('image')) {
                // Backward compatibility: single image upload
                $uploadedImages[] = $request->file('image')->store('products', 'public');
            }

            $data['images'] = $uploadedImages; // Store as JSON array
            if (!empty($uploadedImages)) {
                $data['image'] = $uploadedImages[0]; // Keep first image for backward compatibility
            }

            // mark product active by default so it appears in catalog immediately
            $data['status'] = $data['status'] ?? 'active';
            $product = Product::create($data);
            $product->load(['business.user', 'category', 'reviews.order.buyer']);

            Log::info("Product {$product->id} created by seller {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan!',
                'data' => new ProductResource($product)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product (seller only)
     * PUT /api/products/{id}
     */
    public function update(UpdateProductRequest $request, string $product)
    {
        try {
            $user = $request->user();
            $productModel = Product::with('business')->find($product);

            if (!$productModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Authorization check
            if ($productModel->business->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak diizinkan mengubah produk ini'
                ], 403);
            }

            $data = $request->validated();

            // Jika seller mengubah kategori, simpan category_id dan slug kategori untuk filtering
            if (!empty($data['category_id'])) {
                $category = Category::find($data['category_id']);
                if ($category) {
                    $data['category'] = $category->slug ?: Str::slug($category->name);
                }
            }

            // Handle image uploads (multiple images support) — gabungkan dengan galeri lama, max 5
            if ($request->hasFile('images')) {
                $existing = [];
                if (is_array($productModel->images)) {
                    $existing = array_values(array_filter($productModel->images));
                } elseif ($productModel->image) {
                    $existing = [$productModel->image];
                }
                $uploadedImages = [];
                foreach ($request->file('images') as $imageFile) {
                    if (count($existing) + count($uploadedImages) >= 5) {
                        break;
                    }
                    $uploadedImages[] = $imageFile->store('products', 'public');
                }
                $merged = array_values(array_filter(array_merge($existing, $uploadedImages)));
                $merged = array_slice($merged, 0, 5);
                $data['images'] = $merged;
                $data['image'] = $merged[0] ?? $productModel->image;
            } elseif ($request->hasFile('image')) {
                $existing = [];
                if (is_array($productModel->images)) {
                    $existing = array_values(array_filter($productModel->images));
                } elseif ($productModel->image) {
                    $existing = [$productModel->image];
                }
                if (count($existing) >= 5) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maksimal 5 foto per produk. Hapus foto lama terlebih dahulu.',
                    ], 422);
                }
                $uploadedImage = $request->file('image')->store('products', 'public');
                $merged = array_values(array_filter(array_merge($existing, [$uploadedImage])));
                $merged = array_slice($merged, 0, 5);
                $data['images'] = $merged;
                $data['image'] = $merged[0] ?? $uploadedImage;
            }

            $productModel->update($data);
            $productModel->load(['business.user', 'category']);

            Log::info("Product {$productModel->id} updated by seller {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui!',
                'data' => new ProductResource($productModel)
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product (seller only)
     * DELETE /api/products/{id}
     */
    public function destroy(Request $request, string $product)
    {
        try {
            $user = auth()->user();
            $productModel = Product::with('business')->find($product);

            if (!$productModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Authorization check
            if ($productModel->business->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak diizinkan menghapus produk ini'
                ], 403);
            }

            // Delete image if exists
            if ($productModel->image) {
                Storage::disk('public')->delete($productModel->image);
            }

            $productModel->delete();

            Log::info("Product {$productModel->id} deleted by seller {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by business ID
     * GET /api/businesses/{business}/products
     */
    public function getByBusiness(Request $request, $businessId)
    {
        try {
            $business = Business::where(function ($query) use ($businessId) {
                    $query->where('id', $businessId)
                          ->orWhere('user_id', $businessId);
                })
                ->first();

            if (!$business && !is_numeric($businessId)) {
                $slug = Str::slug($businessId);
                $business = Business::where(function ($query) use ($slug) {
                        $query->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [$slug])
                              ->orWhereHas('user', function ($q) use ($slug) {
                                  $q->whereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [$slug]);
                              });
                    })
                    ->first();
            }

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan',
                    'data' => []
                ], 404);
            }

            // Only show products if business is approved
            if ($business->status !== 'approved') {
                return response()->json([
                    'success' => true,
                    'message' => 'Toko belum disetujui',
                    'data' => []
                ], 200);
            }

            $query = Product::where('business_id', $business->id)
                ->where('status', 'active');

            // Search filter
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            // Sorting
            $sort = $request->sort ?? 'terbaru';
            match($sort) {
                'terbaru' => $query->orderBy('created_at', 'desc'),
                'terlaris' => $query->orderBy('total_sold', 'desc'),
                'termurah' => $query->orderBy('price', 'asc'),
                default => $query->orderBy('created_at', 'desc'),
            };

            $products = $query->with('business.user')->paginate($request->per_page ?? 12);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching business products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil produk toko',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get authenticated seller's products
     * GET /api/seller/products
     */
    public function sellerProducts(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user->business) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anda belum memiliki toko',
                    'data' => []
                ], 200);
            }

            $query = $user->business->products();

            // Search filter
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            }

            $products = $query->with(['business.user', 'category'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 10);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ],
                'business' => $user->business ? new BusinessResource($user->business->load('user')) : null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching seller products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil produk Anda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload product image
     * POST /api/products/{id}/image
     */
    public function uploadImage(Request $request, string $product)
    {
        try {
            $user = auth()->user();
            $productModel = Product::with('business')->find($product);

            if (!$productModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            // Verify product ownership
            if ($productModel->business->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Validate file
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120'
            ], [
                'image.required' => 'Gambar harus diupload',
                'image.image' => 'File harus berupa gambar',
                'image.mimes' => 'Format harus JPEG, PNG, JPG, WebP, atau GIF',
                'image.max' => 'Ukuran maksimal 5MB',
            ]);

            // Tambahkan ke galeri (maks. 5), jangan hapus foto lama
            $existing = [];
            if (is_array($productModel->images)) {
                $existing = array_values(array_filter($productModel->images));
            } elseif ($productModel->image) {
                $existing = [$productModel->image];
            }

            if (count($existing) >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maksimal 5 foto per produk',
                ], 422);
            }

            $path = $request->file('image')->store('products', 'public');
            $merged = array_values(array_merge($existing, [$path]));
            $merged = array_slice($merged, 0, 5);

            $productModel->update([
                'images' => $merged,
                'image' => $merged[0] ?? $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gambar produk berhasil diupload',
                'data' => [
                    'image' => $productModel->image,
                    'images' => $merged,
                    'image_url' => ImageUrl::normalize($productModel->image),
                    'gallery_urls' => array_map(fn ($p) => ImageUrl::normalize($p), $merged),
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product image upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload gambar produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product image from gallery
     * DELETE /api/products/{id}/image
     * Accepts JSON: { image: 'path/to/file.jpg' } or { index: 0 }
     */
    public function deleteImage(Request $request, string $product)
    {
        try {
            $user = auth()->user();
            $productModel = Product::with('business')->find($product);

            if (!$productModel) {
                return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
            }

            // Verify ownership
            if ($productModel->business->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $images = [];
            if (is_array($productModel->images)) {
                $images = array_values(array_filter($productModel->images));
            } elseif ($productModel->image) {
                $images = [$productModel->image];
            }

            if (empty($images)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada gambar untuk dihapus'], 422);
            }

            $pathToDelete = null;

            if ($request->has('index')) {
                $idx = (int) $request->input('index');
                if (!isset($images[$idx])) {
                    return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan pada index tersebut'], 404);
                }
                $pathToDelete = $images[$idx];
                unset($images[$idx]);
            } elseif ($request->has('image')) {
                $img = $request->input('image');
                // Try exact match first
                $key = array_search($img, $images);
                // If not exact, try to convert URL to storage path (strip domain and /storage/)
                if ($key === false) {
                    $candidate = null;
                    // If it's a full URL, extract path
                    if (filter_var($img, FILTER_VALIDATE_URL)) {
                        $urlPath = parse_url($img, PHP_URL_PATH) ?: $img;
                        // remove leading /storage/ or /storage
                        $candidate = preg_replace('#^.*?/storage/#', '', $urlPath);
                    } else {
                        // If starts with /storage/ or storage/, strip it
                        $candidate = preg_replace('#^/?storage/#', '', $img);
                    }

                    if ($candidate) {
                        $key = array_search($candidate, $images);
                        if ($key !== false) {
                            $pathToDelete = $images[$key];
                            unset($images[$key]);
                        }
                    }

                    if ($key === false) {
                        return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan'], 404);
                    }
                } else {
                    $pathToDelete = $images[$key];
                    unset($images[$key]);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Parameter image atau index diperlukan'], 422);
            }

            // Remove file from storage if exists
            if ($pathToDelete) {
                Storage::disk('public')->delete($pathToDelete);
            }

            $images = array_values($images);
            $productModel->images = $images;
            $productModel->image = $images[0] ?? null;
            $productModel->save();

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dihapus',
                'data' => [
                    'images' => $images,
                    'image' => $productModel->image,
                    'gallery_urls' => array_map(fn($p) => ImageUrl::normalize($p), $images),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Product image delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus gambar', 'error' => $e->getMessage()], 500);
        }
    }
}