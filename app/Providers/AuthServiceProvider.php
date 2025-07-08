<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\Payment;
use App\Policies\PaymentPolicy;
use App\Models\Cupon;
use App\Policies\CuponPolicy;
use App\Models\Addresse;
use App\Policies\AddressePolicy;
use App\Models\Contact;
use App\Policies\ContactPolicy;
use App\Models\Comment;
use App\Policies\CommentPolicy;
use App\Models\Review;
use App\Policies\ReviewPolicy;
use App\Models\ProductVariant;
use App\Policies\ProductVariantPolicy;
use App\Models\ProductFile;
use App\Policies\ProductFilePolicy;
use App\Models\OrderItem;
use App\Policies\OrderItemPolicy;
use App\Models\SavedProduct;
use App\Policies\SavedProductPolicy;
use App\Models\Categorie;
use App\Policies\CategoryTagPolicy;
use App\Models\Tag;
use App\Models\User;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        Payment::class => PaymentPolicy::class,
        Cupon::class => CuponPolicy::class,
        Addresse::class => AddressePolicy::class,
        Contact::class => ContactPolicy::class,
        Comment::class => CommentPolicy::class,
        Review::class => ReviewPolicy::class,
        ProductVariant::class => ProductVariantPolicy::class,
        ProductFile::class => ProductFilePolicy::class,
        OrderItem::class => OrderItemPolicy::class,
        SavedProduct::class => SavedProductPolicy::class,
        Categorie::class => CategoryTagPolicy::class,
        Tag::class => CategoryTagPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
} 