<?php

namespace Database\Seeders;

use App\Models\{
    User,
    Product,
    Order,
    Payment,
    ProductVariant,
    ProductFile,
    Comment,
    Review,
    SavedProduct,
    OrderItem,
    Tag,
    Cupon,
    Categorie,
    Addresse,
    Contact
};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 
        $users = User::factory()->count(10)->create();

        // 
        $products = Product::factory()->count(20)
            ->withDiscount()
            ->create();

        $inactiveProducts = Product::factory()->count(10)
            ->inactive()
            ->create();

        // 
        foreach ($products->random(5) as $product) {
            ProductVariant::factory()->count(3)->create([
                'product_id' => $product->id
            ]);
        }

        // 
        foreach ($products->random(8) as $product) {
            ProductFile::factory()->count(2)->image()->create([
                'product_id' => $product->id
            ]);
            ProductFile::factory()->document()->create([
                'product_id' => $product->id
            ]);
        }

        // 
        foreach ($products->random(15) as $product) {
            Review::factory()->count(rand(2, 8))
                ->create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id
                ]);
        }

        // 
        foreach ($products->random(12) as $product) {
            Comment::factory()->count(rand(3, 10))
                ->create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id
                ]);
        }

        // 
        foreach ($users as $user) {
            $userProducts = $products->random(rand(0, 5));
            foreach ($userProducts as $product) {
                SavedProduct::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id
                ]);
            }
        }

        // 
        $orders = Order::factory()->count(10)->create();

        foreach ($orders as $order) {
            $orderProducts = $products->random(rand(1, 4));
            foreach ($orderProducts as $product) {
                $variant = $product->variants->isNotEmpty()
                    ? $product->variants->random()
                    : null;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'unit_price' => $variant ? $variant->price : $product->price,
                    'quantity' => rand(1, 3)
                ]);
            }
        }

        // 
        Order::factory()->count(2)
            ->cancelled()
            ->create();

        // 
        Payment::factory()->count(5)
            ->succeeded()
            ->create();

        Payment::factory()->count(2)
            ->failed()
            ->create();

        // 

        // 
        Tag::factory()->count(10)->create();

        // 
        Cupon::factory()->count(5)->create();

        // 
        Categorie::factory()->count(8)->create();

        // 
        foreach ($users as $user) {
            Addresse::factory()->count(rand(1, 3))->create([
                'user_id' => $user->id
            ]);
        }

        // 
        foreach ($users as $user) {
            Contact::factory()->count(rand(1, 2))->create([
                'user_id' => $user->id
            ]);
        }
    }
}
