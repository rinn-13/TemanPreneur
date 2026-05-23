<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\IssueReportController;
use App\Http\Controllers\Api\Admin\VerificationController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\AccessCodeController;
use App\Http\Controllers\Api\Admin\PerformanceController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SellerProfileController;
use Illuminate\Support\Facades\Route;



// Public routes
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
// ✅ WAJIB: letakkan DI ATAS sebelum {businessId}
Route::middleware('auth:sanctum')->get('/businesses/me', [BusinessController::class, 'me']);
Route::get('/businesses/all', [BusinessController::class, 'all']);
Route::get('/businesses/{businessId}/products', [ProductController::class, 'getByBusiness']);
Route::get('/businesses/{businessId}', [BusinessController::class, 'publicShow']);
Route::get('/business/{businessId}', [BusinessController::class, 'publicShow']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/reviews/top-sellers', [ReviewController::class, 'topSellers']);

// Public seller profile routes
Route::get('/seller/{userId}/profile', [SellerProfileController::class, 'publicProfile']);
Route::get('/seller/{userId}/store', [SellerProfileController::class, 'publicProfile']); // Alias

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Blogs - Public
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{blog}', [BlogController::class, 'show']);
Route::get('/businesses/{business}/blogs', [BlogController::class, 'getByBusiness']);

// Seller routes
Route::middleware('auth:sanctum')->prefix('seller')->name('seller.')->group(function () {
    // Profile & Sync
    Route::get('/profile', [SellerProfileController::class, 'profileSync']);
    Route::get('/me', [SellerProfileController::class, 'profileSync']); // Alias
    
    // Dashboard & Analytics
    Route::get('/dashboard', [App\Http\Controllers\Api\Seller\DashboardController::class, 'index']);
    Route::get('/analytics', [App\Http\Controllers\Api\Seller\DashboardController::class, 'analytics']);
    Route::get('/revenue', [App\Http\Controllers\Api\Seller\DashboardController::class, 'revenue']);
    Route::get('/orders', [App\Http\Controllers\Api\Seller\DashboardController::class, 'orders']);
    
    // Wallet
    Route::get('/wallet', [App\Http\Controllers\Api\Seller\WalletController::class, 'index']);
    Route::post('/wallet/withdraw', [App\Http\Controllers\Api\Seller\WalletController::class, 'withdraw']);
    
    // Blog CRUD
    Route::get('/blogs', [BlogController::class, 'sellerIndex']);
    Route::apiResource('blogs', BlogController::class)->only(['store', 'update', 'destroy']);
});

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade']);

    // Users - admin-only REST resource
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class)->except(['create', 'edit']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Business - standard REST API resources and custom endpoints
    Route::get('/business/pengaturan', [BusinessController::class, 'settings']);
    Route::get('/businesses/pengaturan', [BusinessController::class, 'settings']);
    Route::get('/businesses/dashboard', [BusinessController::class, 'dashboard']);
    Route::get('/businesses/products', [BusinessController::class, 'products']);
    Route::get('/businesses/pengaturan/products', [BusinessController::class, 'settingsProducts']);
    Route::get('/businesses/pengaturan/blogs', [BusinessController::class, 'settingsBlogs']);
    Route::post('/businesses/{business}/logo', [BusinessController::class, 'uploadLogo']);
    Route::post('/businesses/{business}/banner', [BusinessController::class, 'uploadBanner']);
    Route::apiResource('businesses', BusinessController::class)->only(['index', 'store', 'update']);

    // Products (only authenticated users)
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::post('/products/{product}/image', [ProductController::class, 'uploadImage']);
    Route::delete('/products/{product}/image', [ProductController::class, 'deleteImage']);
    Route::get('/seller/products', [ProductController::class, 'sellerProducts']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
    Route::get('/cart/count', [CartController::class, 'count']);

    // Orders - standard REST API resources plus custom order actions
    Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store']);
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice']);
    Route::get('/orders/{order}/invoice/pdf', [OrderController::class, 'invoicePdf']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders/{order}/track', [OrderController::class, 'track']);
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);

    // Chat
    Route::get('/orders/{order}/chat', [ChatController::class, 'index']);
    Route::post('/orders/{order}/chat', [ChatController::class, 'store']);

    // ✅ FIXED: Wishlist - All HTTP methods supported
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product_id}', [WishlistController::class, 'destroy']);

    // ✅ ADDED: Favorites - Simple alternative to wishlist
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/check/{productId}', [FavoriteController::class, 'check']);
    Route::get('/favorites/count', [FavoriteController::class, 'count']);

    // ✅ ADDED: Reviews - All endpoints
    Route::get('/reviews/my', [ReviewController::class, 'my']);
    Route::get('/reviews/product/{productId}', [ReviewController::class, 'product']);
    Route::get('/reviews/seller/{businessId}/stats', [ReviewController::class, 'sellerStats']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    Route::get('/reviews', [ReviewController::class, 'index']);

    // Issue Reports
    Route::get('/reports', [IssueReportController::class, 'index']);
    Route::post('/reports', [IssueReportController::class, 'store']);
    Route::get('/reports/{report}', [IssueReportController::class, 'show']);
    Route::patch('/reports/{report}/resolve', [IssueReportController::class, 'buyerResolve']);
    Route::get('/reports/responses/mine', [IssueReportController::class, 'getSellerResponses']);
    Route::get('/reports/responses/{response}', [IssueReportController::class, 'getResponseDetail']);
    Route::post('/reports/responses/{response}/confirm', [IssueReportController::class, 'confirmResponseReceived']);
    Route::patch('/reports/responses/{response}/complete', [IssueReportController::class, 'completeResponseByUser']);

    // Profile
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/user/upload-photo', [AuthController::class, 'uploadPhoto']);

    // Daftar sebagai buyer (untuk seller yang ingin membeli)
    Route::post('/user/register-as-buyer', [AuthController::class, 'registerAsBuyer']);

    // Seller stats (bisa di-access oleh seller)
    Route::get('/seller/stats', [StatsController::class, 'sellerStats']);

    // Team management (premium seller)
    Route::middleware(['auth:sanctum', 'premium'])->prefix('business/{business}/team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index']);
        Route::post('/', [TeamController::class, 'store']);
        Route::get('/{teamMember}', [TeamController::class, 'show']);
        Route::put('/{teamMember}', [TeamController::class, 'update']);
        Route::delete('/{teamMember}', [TeamController::class, 'destroy']);
    });

    // Team Invitations
    Route::middleware('auth:sanctum')->prefix('team-invitations')->name('team-invitations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\TeamInvitationController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\TeamInvitationController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\TeamInvitationController::class, 'show']);
        Route::post('/{id}/accept', [App\Http\Controllers\Api\TeamInvitationController::class, 'accept']);
        Route::post('/{id}/decline', [App\Http\Controllers\Api\TeamInvitationController::class, 'decline']);
    });

    // Admin only
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/businesses', [BusinessController::class, 'index']); 
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
        
        
        // Access Codes Management
        Route::get('/access-codes', [AccessCodeController::class, 'index']);
        Route::post('/access-codes', [AccessCodeController::class, 'store']);
        Route::get('/access-codes/{id}', [AccessCodeController::class, 'show']);
        Route::delete('/access-codes/{id}', [AccessCodeController::class, 'destroy']);
        Route::get('/access-codes/check/{code}', [AccessCodeController::class, 'check']);
        
        // Business Verification (Pending -> Verified/Rejected) - STEP 1
        Route::apiResource('verifications', VerificationController::class)->only(['index', 'show', 'update', 'destroy']);
        
        // Business Approval/Rejection
        Route::post('/businesses/{business}/verify', [BusinessController::class, 'verify']);
        Route::post('/businesses/{business}/upgrade', [BusinessController::class, 'upgrade']);
        Route::post('/businesses/{business}/approve', [BusinessController::class, 'approve']);
        Route::post('/businesses/{business}/reject', [BusinessController::class, 'reject']);
        Route::post('/businesses/{business}/block', [BusinessController::class, 'block']);
        Route::post('/businesses/{business}/unblock', [BusinessController::class, 'unblock']);
        
        // Admin stats and products (monitoring only)
        Route::get('/stats', [StatsController::class, 'index']);
        // Admin product monitoring: return all products without filtering
        Route::get('/products', [ProductController::class, 'adminIndex']);
        // Admin delete (mark as removed)
        Route::delete('/products/{id}', [ProductController::class, 'adminDelete']);
        Route::get('/blogs', [BlogController::class, 'index']);
        Route::get('/reports', [IssueReportController::class, 'adminIndex']);
        Route::patch('/reports/{report}', [IssueReportController::class, 'adminUpdateStatus']);
        Route::post('/reports/{report}/respond', [IssueReportController::class, 'adminRespond']);
        Route::patch('/reports/responses/{response}/complete', [IssueReportController::class, 'completeResponse']);
        Route::get('/businesses/{businessId}/products', [ProductController::class, 'getByBusiness']);
        Route::get('/seller/products', [ProductController::class, 'sellerProducts']);
        Route::get('/seller/stats', [StatsController::class, 'sellerStats']);
        Route::put('/settings/general', [SettingsController::class, 'updateGeneral']);
        Route::put('/settings/transaction', [SettingsController::class, 'updateTransaction']);
        Route::post('/maintenance', [SettingsController::class, 'toggleMaintenance']);
        Route::get('/performance', [PerformanceController::class, 'index']);
        Route::get('/performance/export', [PerformanceController::class, 'export']);
    });
});
