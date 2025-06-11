<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Order, Payment, Cupon};

class OrderSeeder extends Seeder
{
    // 
    private const STATUS_MAP = [
        'paid' => 'succeeded',
        'unpaid' => 'pending',
        'partially_paid' => 'pending',
        'refunded' => 'refunded'
    ];

    public function run(): void
    {
        // 
        $coupons = Cupon::factory()
            ->count(5)
            ->sequence(
                ['is_active' => true, 'expires_at' => now()->addYear()],
                ['is_active' => true, 'expires_at' => now()->addMonth()],
                ['is_active' => true, 'max_usage' => 5],
                ['is_active' => false],
                ['expires_at' => now()->subDay()]
            )
            ->create();

        //
        Order::factory()
            ->count(3)
            ->paid()
            ->delivered()
            ->afterCreating(function ($order) use ($coupons) {
                $this->applyCouponToOrder($order, $coupons);
                $this->createPayment($order);
            })
            ->create();

        Order::factory()
            ->count(2)
            ->cancelled()
            ->afterCreating(function ($order) {
                $this->createPayment($order, 'failed');
            })
            ->create();

        // 
        Order::factory()
            ->afterCreating(function ($order) use ($coupons) {
                $order->update(['coupon_id' => $coupons->where('is_active', true)->first()->id]);
                $this->createPayment($order, 'pending');
            })
            ->create();
    }

    private function applyCouponToOrder($order, $coupons): void
    {
        if (rand(0, 1)) {
            $coupon = $coupons->where('is_active', true)
                ->where('expires_at', '>', now())
                ->random();

            $order->update([
                'coupon_id' => $coupon->id,
                'total' => max($order->total - $coupon->value, 0)
            ]);
        }
    }

    private function createPayment($order, string $status = null): void
    {
        $status = $status ?? self::STATUS_MAP[$order->payment_status] ?? 'pending';

        Payment::factory()
            ->state([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->total,
                'status' => $status,
                'processed_at' => $status === 'succeeded' ? $order->paid_at : null
            ])
            ->when($status === 'failed', fn($factory) => $factory->failed())
            ->when($status === 'refunded', fn($factory) => $factory->refunded())
            ->create();
    }
}