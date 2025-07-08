<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        parent::boot();

        // Add explicit model bindings
        Route::model('payment', \App\Models\Payment::class);
        Route::model('order', \App\Models\Order::class);
        Route::model('coupon', \App\Models\Cupon::class);
        Route::model('address', \App\Models\Addresse::class);
        Route::model('contact', \App\Models\Contact::class);
        Route::model('product', \App\Models\Product::class);
        Route::model('comment', \App\Models\Comment::class);
        Route::model('review', \App\Models\Review::class);
        Route::model('variant', \App\Models\ProductVariant::class);
        Route::model('file', \App\Models\ProductFile::class);
        Route::model('orderitem', \App\Models\OrderItem::class);
        Route::model('savedproduct', \App\Models\SavedProduct::class);
        Route::model('category', \App\Models\Categorie::class);
        Route::model('tag', \App\Models\Tag::class);

        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAuthRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware([
                'json',
                \App\Http\Middleware\ForceJsonResponse::class
            ])
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
    
    protected function mapAuthRoutes(): void
    {
        Route::prefix('Auth')
            ->middleware(['api', 'json'])
            ->namespace($this->namespace)
            ->group(base_path('routes/auth.php'));
    }
}