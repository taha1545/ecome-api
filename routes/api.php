<?php

use App\Http\Controllers\{UserController, ProductController, OrderController, PaymentController, CouponController, AddressController, OrderItemController, ContactController, StockController};
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\CategoryTagController;
use App\Http\Controllers\ProductFileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SavedProductController;
use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// Public
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/best-selling', [ProductController::class, 'bestSelling']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/suggest/{id}', [ProductController::class, 'suggestProducts']);
    Route::get('/categories', [CategoryTagController::class, 'getCategories']);
    Route::get('/tags', [CategoryTagController::class, 'getTags']);
    Route::get('/category/{categoryId}', [CategoryTagController::class, 'getProductsByCategory']);
    Route::get('/tag/{tagId}', [CategoryTagController::class, 'getProductsByTag']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::get('/{productId}/comments', [CommentController::class, 'getComments']);
    Route::get('/{productId}/reviews', [ReviewController::class, 'getReviews']);
    Route::get('/{productId}/files', [ProductFileController::class, 'getFiles']);
    Route::get('/{productId}/variants', [ProductVariantController::class, 'getVariants']);
});

// Stock Management Routes
Route::prefix('stock')->group(function () {
    Route::get('/highest', [StockController::class, 'highestStock']);
    Route::get('/lowest', [StockController::class, 'lowestStock']);
    Route::get('/out-of-stock', [StockController::class, 'outOfStock']);
});

// Protected
Route::middleware('auth.api:sanctum')->group(function () {

    // Product routes
    Route::prefix('products')->group(function () {
        // CRUD
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);

        // Comments
        Route::post('/{productId}/comments', [CommentController::class, 'addComment']);
        Route::delete('/{productId}/comments/{commentId}', [CommentController::class, 'deleteComment']);

        // Reviews
        Route::get('/{productId}/reviews', [ReviewController::class, 'getReviews']);
        Route::post('/{productId}/reviews', [ReviewController::class, 'reviewProduct']);

        // Files
        Route::post('/{productId}/files', [ProductFileController::class, 'addFile']);
        Route::delete('/{productId}/files/{fileId}', [ProductFileController::class, 'deleteFile']);

        // Variants
        Route::post('/{productId}/variants', [ProductVariantController::class, 'addVariant']);
        Route::put('/{productId}/variants/{variantId}', [ProductVariantController::class, 'updateVariant']);
        Route::delete('/{productId}/variants/{variantId}', [ProductVariantController::class, 'deleteVariant']);
        Route::patch('/{productId}/variants/{variantId}/stock', [ProductVariantController::class, 'updateStock']);

        // Tags and Categories
        Route::post('/tags', [CategoryTagController::class, 'createTag']);
        Route::delete('/tags/{tagId}', [CategoryTagController::class, 'deleteTag']);
        Route::post('/categories', [CategoryTagController::class, 'createCategory']);
        Route::delete('/categories/{categoryId}', [CategoryTagController::class, 'deleteCategory']);
        Route::post('/{productId}/tags', [CategoryTagController::class, 'addTag']);
        Route::post('/{productId}/categories', [CategoryTagController::class, 'addCategory']);

        // Saved products
        Route::get('/saved', [SavedProductController::class, 'getSavedProducts']);
        Route::post('/{productId}/save', [SavedProductController::class, 'toggleSaveProduct']);
        Route::get('/{productId}/is-saved', [SavedProductController::class, 'isProductSaved']);
    });


    // Payment routes
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::get('/payments/{id}/status', [PaymentController::class, 'getPaymentStatus']);
    Route::patch('/payments/{id}/status', [PaymentController::class, 'updateStatus']);
    Route::post('/payments/{id}/refund', [PaymentController::class, 'refund']);
    Route::post('/payments/webhook', [PaymentController::class, 'handleWebhook']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Order Item routes
    Route::get('/orders/{orderId}/items', [OrderItemController::class, 'index']);
    Route::get('/order-items/{id}', [OrderItemController::class, 'show']);

    // Coupon routes
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::get('/coupons/{id}', [CouponController::class, 'show']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::put('/coupons/{id}', [CouponController::class, 'update']);
    Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
    Route::post('/orders/{id}/use-coupon', [CouponController::class, 'addCouponToOrder']);

    // Address routes
    Route::get('/addresses', [AddressController::class, 'getUserAddresses']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    // Contact routes
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::put('/contacts/{id}', [ContactController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);
});

// Admin Dashboard Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/summary', [AdminDashboardController::class, 'summary']);
    Route::get('/sales-analytics', [AdminDashboardController::class, 'salesAnalytics']);
    Route::get('/order-analytics', [AdminDashboardController::class, 'orderAnalytics']);
    Route::get('/order-notifications', [AdminDashboardController::class, 'getOrderNotifications']);
});
