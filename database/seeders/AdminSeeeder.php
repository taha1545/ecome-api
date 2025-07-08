<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    Chart,
    ChartItem
};

class AdminSeeeder extends Seeder
{

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'admin123'
        ]);
    }
}
