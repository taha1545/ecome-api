<?php

use App\Http\Controllers\{
    UserController,
    ProductController,
    OrderController,
    PaymentController,
    CouponController,
    AddressController,
    OrderItemController,
    ContactController,
    StockController
};
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\CategoryTagController;
use App\Http\Controllers\ProductFileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SavedProductController;
use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// products
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/best-selling', [ProductController::class, 'bestSelling']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/suggest/{id}', [ProductController::class, 'suggestProducts']);
    Route::get('/categories', [CategoryTagController::class, 'getCategories']);
    Route::get('/tags', [CategoryTagController::class, 'getTags']);
    Route::get('/saved', [SavedProductController::class, 'getSavedProducts'])->middleware(['auth.api']);
    Route::get('/category/{categoryId}', [CategoryTagController::class, 'getProductsByCategory']);
    Route::get('/tag/{tagId}', [CategoryTagController::class, 'getProductsByTag']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::get('/{productId}/comments', [CommentController::class, 'getComments']);
    Route::get('/{productId}/reviews', [ReviewController::class, 'getReviews']);
    Route::get('/{productId}/files', [ProductFileController::class, 'getFiles']);
    Route::get('/{productId}/variants', [ProductVariantController::class, 'getVariants']);
});

// Protected Routes
Route::middleware(['auth.api'])->group(function () {

    // Product routes
    Route::prefix('products')->group(function () {
        // CRUD
        Route::post('/', [ProductController::class, 'store'])
            ->middleware('can:create,App\Models\Product');
        Route::put('/{product}', [ProductController::class, 'update'])
            ->middleware('can:update,product');
        Route::delete('/{product}', [ProductController::class, 'destroy'])
            ->middleware('can:delete,product');

        // Comments
        Route::post('/{product}/comments', [CommentController::class, 'addComment'])
            ->middleware('can:create,App\Models\Comment,product');

        Route::delete('/{product}/comments/{comment}', [CommentController::class, 'deleteComment'])
            ->middleware('can:delete,comment');

        // Reviews
        Route::post('/{product}/reviews', [ReviewController::class, 'reviewProduct'])
            ->middleware('can:reviewProduct,App\Models\Review,product');

        // Files
        Route::post('/{product}/files', [ProductFileController::class, 'addFile'])
            ->middleware('can:create,App\Models\ProductFile');
        Route::delete('/{product}/files/{file}', [ProductFileController::class, 'deleteFile'])
            ->middleware('can:delete,file');

        // Variants
        Route::post('/{product}/variants', [ProductVariantController::class, 'addVariant'])
            ->middleware('can:create,App\Models\ProductVariant');

        Route::put('/{product}/variants/{variant}', [ProductVariantController::class, 'updateVariant'])
            ->middleware('can:update,variant');
        Route::delete('/{product}/variants/{variant}', [ProductVariantController::class, 'deleteVariant'])
            ->middleware('can:delete,variant');
        Route::patch('/{product}/variants/{variant}/stock', [ProductVariantController::class, 'updateStock'])
            ->middleware('can:updateStock,variant');

        // Tags and Categories
        Route::post('/tags', [CategoryTagController::class, 'createTag'])
            ->middleware('can:createTag,App\Models\Tag');
        Route::delete('/tags/{tag}', [CategoryTagController::class, 'deleteTag'])
            ->middleware('can:deleteTag,tag');
        Route::post('/categories', [CategoryTagController::class, 'createCategory'])
            ->middleware('can:createCategory,App\Models\Categorie');

        Route::delete('/categories/{category}', [CategoryTagController::class, 'deleteCategory'])
            ->middleware('can:deleteCategory,category');

        Route::post('/{product}/tags', [CategoryTagController::class, 'addTag'])
            ->middleware('can:addTag,App\Models\Tag');

        Route::post('/{productId}/categories', [CategoryTagController::class, 'addCategory'])
            ->middleware('can:addCategory,App\Models\Categorie');

        // Saved products
        Route::post('/{product}/save', [SavedProductController::class, 'toggleSaveProduct'])
            ->middleware('can:toggleSave,App\Models\SavedProduct,product');

        Route::get('/{productId}/is-saved', [SavedProductController::class, 'isProductSaved'])
            ->middleware('can:checkSaved,App\Models\Product');
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])
            ->middleware('can:viewAny,App\Models\Payment');
        Route::post('/', [PaymentController::class, 'store'])
            ->middleware('can:create,App\Models\Payment');
        Route::get('/{payment}', [PaymentController::class, 'show'])
            ->middleware('can:view,payment');
        Route::get('/{payment}/status', [PaymentController::class, 'getPaymentStatus'])
            ->middleware('can:view,payment');
        Route::patch('/{payment}/status', [PaymentController::class, 'updateStatus'])
            ->middleware('can:updateStatus,payment');
        Route::put('/{payment}', [PaymentController::class, 'update'])
            ->middleware('can:update,payment');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])
            ->middleware('can:delete,payment');
    });

    // Order routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])
            ->middleware('can:viewAny,App\Models\Order');
        Route::post('/', [OrderController::class, 'store'])
            ->middleware('can:create,App\Models\Order');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])
            ->middleware('can:cancel,order');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])
            ->middleware('can:updateStatus,order');
        Route::get('/{order}', [OrderController::class, 'show'])
            ->middleware('can:view,order');
        Route::put('/{order}', [OrderController::class, 'update'])
            ->middleware('can:update,order');
        Route::delete('/{order}', [OrderController::class, 'destroy'])
            ->middleware('can:delete,order');
    });

    // Order Item routes
    Route::prefix('order-items')->group(function () {
        Route::get('/orders/{orderId}/items', [OrderItemController::class, 'index'])
            ->middleware('can:viewAny,App\Models\OrderItem');
        Route::get('/{orderitem}', [OrderItemController::class, 'show'])
            ->middleware('can:view,orderitem');
    });

    // Coupon routes
    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index'])
            ->middleware('can:viewAny,App\Models\Cupon');
        Route::get('/{coupon}', [CouponController::class, 'show'])
            ->middleware('can:view,coupon');
        Route::post('/', [CouponController::class, 'store'])
            ->middleware('can:create,App\Models\Cupon');
        Route::put('/{coupon}', [CouponController::class, 'update'])
            ->middleware('can:update,coupon');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])
            ->middleware('can:delete,coupon');
        Route::post('/validate', [CouponController::class, 'validateCoupon'])
            ->middleware('can:apply,App\Models\Cupon');
        Route::post('/orders/{order}/use-coupon', [CouponController::class, 'addCouponToOrder'])
            ->middleware('can:apply,App\Models\Cupon');
    });

    // Address routes
    Route::prefix('addresses')->group(function () {

        Route::get('/', [AddressController::class, 'getUserAddresses']);

        Route::get('/{address}', [AddressController::class, 'show'])
            ->middleware('can:view,address');

        Route::post('/', [AddressController::class, 'store'])
            ->middleware('can:create,App\Models\Addresse');

        Route::put('/{address}', [AddressController::class, 'update'])
            ->middleware('can:update,address');

        Route::delete('/', [AddressController::class, 'destroy']);
    });

    // Contact routes
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index'])
            ->middleware('can:viewAny,App\Models\Contact');
        Route::post('/', [ContactController::class, 'store'])
            ->middleware('can:create,App\Models\Contact');
        Route::get('/{contact}', [ContactController::class, 'show'])
            ->middleware('can:view,contact');
        Route::put('/{contact}', [ContactController::class, 'update'])
            ->middleware('can:update,contact');
        Route::delete('/{contact}', [ContactController::class, 'destroy'])
            ->middleware('can:delete,contact');
        Route::patch('/{contact}/primary', [ContactController::class, 'setPrimary'])
            ->middleware('can:setPrimary,contact');
    });
});

// Admin Dashboard Routes
Route::prefix('admin')->middleware(['auth.api'])->group(function () {
    Route::get('/summary', [AdminDashboardController::class, 'summary'])
        ->middleware('can:viewAny,App\Models\User');
    Route::get('/sales-analytics', [AdminDashboardController::class, 'salesAnalytics'])
        ->middleware('can:viewAny,App\Models\User');
    Route::get('/order-analytics', [AdminDashboardController::class, 'orderAnalytics'])
        ->middleware('can:viewAny,App\Models\User');
    Route::get('/order-notifications', [AdminDashboardController::class, 'getOrderNotifications'])
        ->middleware('can:viewAny,App\Models\User');
});

// Stock management routes
Route::prefix('stock')->middleware(['auth.api'])->group(function () {
    Route::get('/highest', [StockController::class, 'highestStock'])
        ->middleware('can:viewAny,App\Models\User');
    Route::get('/lowest', [StockController::class, 'lowestStock'])
        ->middleware('can:viewAny,App\Models\User');
    Route::get('/out-of-stock', [StockController::class, 'outOfStock'])
        ->middleware('can:viewAny,App\Models\User');
});
