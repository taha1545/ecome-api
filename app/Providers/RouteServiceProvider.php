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
            ->middleware(['api', 'json'])
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
