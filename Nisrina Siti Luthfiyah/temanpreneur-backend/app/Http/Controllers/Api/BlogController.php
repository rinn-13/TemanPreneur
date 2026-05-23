<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Policies\BlogPolicy;

class BlogController extends Controller
{
    // Public blog list and detail
    public function index(Request $request)
    {
        // Business has `category` as a string column, not a relation — eager-loading
        // `business.category` causes SQL errors (500).
        $query = Blog::with(['business.user'])
            ->when($request->business_id, fn($q) => $q->where('business_id', $request->business_id))
            ->when($request->category, fn($q) => $q->whereHas('business', fn($q2) => $q2->where('category', $request->category)))
            ->latest();

        $blogs = $query->paginate(9);

        return BlogResource::collection($blogs);
    }

    public function getByBusiness(Request $request, $businessId)
    {
        $business = Business::where('id', $businessId)
            ->orWhere('user_id', $businessId)
            ->firstOrFail();

        $blogs = Blog::with(['business.user'])
            ->where('business_id', $business->id)
            ->latest()
            ->paginate(9);

        return BlogResource::collection($blogs);
    }

    public function show(Blog $blog)
    {
        $blog->load(['business.user', 'business']);
        return new BlogResource($blog);
    }

    // Seller protected CRUD (scoped to own business)
    public function sellerIndex()
    {
        $business = auth()->user()->business;
        if (!$business) {
            return response()->json(['message' => 'No business found'], 404);
        }

        $blogs = $business->blogs()->with('business')->latest()->paginate(10);
        return BlogResource::collection($blogs);
    }

    public function store(StoreBlogRequest $request)
    {
        $business = auth()->user()->business()->first();
        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada toko terdaftar untuk pengguna ini',
            ], 404);
        }

        if (!$business->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Toko harus terverifikasi sebelum dapat membuat blog',
            ], 403);
        }

        $this->authorize('create', Blog::class);

        $data = $request->validated();
        $data['business_id'] = $business->id;

        if (Schema::hasColumn('blogs', 'excerpt')) {
            if (empty($data['excerpt'])) {
                $data['excerpt'] = substr(strip_tags($data['content'] ?? ''), 0, 120);
            }
        } else {
            unset($data['excerpt']);
        }

        if (Schema::hasColumn('blogs', 'slug')) {
            $slugBase = \Illuminate\Support\Str::slug($data['title']);
            $slug = $slugBase;
            $suffix = 1;
            while (Blog::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $suffix++;
            }
            $data['slug'] = $slug;
        } else {
            unset($data['slug']);
        }

        if (Schema::hasColumn('blogs', 'image') && $request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs/images', 'public');
        } else {
            unset($data['image']);
        }

        try {
            $blog = Blog::create($data);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan blog',
                'error' => $e->getMessage(),
            ], 500);
        }

        return new BlogResource($blog->load('business.user'));
    }

    public function update(StoreBlogRequest $request, Blog $blog)
    {
        $this->authorize('update', $blog);

        $data = $request->validated();

        if (Schema::hasColumn('blogs', 'excerpt')) {
            if (empty($data['excerpt'])) {
                $data['excerpt'] = substr(strip_tags($data['content'] ?? ''), 0, 120);
            }
        } else {
            unset($data['excerpt']);
        }

        if (Schema::hasColumn('blogs', 'slug')) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        } else {
            unset($data['slug']);
        }

        if (Schema::hasColumn('blogs', 'image') && $request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs/images', 'public');
        } else {
            unset($data['image']);
        }

        $blog->update($data);
        return new BlogResource($blog->fresh()->load('business.user'));
    }

    public function destroy(Blog $blog)
    {
        $this->authorize('delete', $blog);

        $blog->delete();
        return response()->json(['message' => 'Blog deleted']);
    }
}

