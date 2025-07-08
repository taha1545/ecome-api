<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class OrderService
{
    protected $taxRate;
    protected $baseShippingCost;

    public function __construct()
    {
        $this->taxRate = Config::get('order.tax_rate', 0.05);
        $this->baseShippingCost = Config::get('order.base_shipping_cost', 100);
    }

    public function createOrder(array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($data['user_id']);

            if (empty($user->addresse)) {
                throw new \Exception('User does not have a shipping address');
            }

            $orderDetails = $this->calculateOrderDetails($data);
            $this->updateStock($data['items']);

            $order = Order::create([
                'user_id' => $user->id,
                'coupon_id' => $orderDetails['coupon_id'],
                'status' =>  'pending',
                'subtotal' => $orderDetails['subtotal'],
                'tax' => $orderDetails['tax'],
                'shipping_cost' => $this->baseShippingCost,
                'total' => $orderDetails['total'],
            ]);

            foreach ($orderDetails['items'] as $item) {
                $order->items()->create($item);
            }

            if ($orderDetails['coupon']) {
                $orderDetails['coupon']->increment('used_count');
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateOrderDetails(array $data)
    {
        $subtotal = 0;
        $discount = 0;
        $items = [];

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $variant = null;

            //
            if (!empty($item['product_variant_id'])) {
                $variant = ProductVariant::where('id', $item['product_variant_id'])
                    ->where('product_id', $product->id)
                    ->firstOrFail();
                //
                $price = $variant->price;
                //
            } else {
                $price = $product->discount_price
                    ? $product->price * ($product->discount_price / 100)
                    : $product->price;
            }

            $itemTotal = $price * $item['quantity'];
            $subtotal += $itemTotal;

            $items[] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id ?? null,
                'unit_price' => $price,
                'quantity' => $item['quantity'],
                'discount_amount' => 0,
            ];
        }

        // 
        $coupon = $this->getValidCoupon($data['coupon_code'] ?? null, $subtotal);
        $couponId = null;

        if ($coupon) {
            $couponId = $coupon->id;
            $discount = ($coupon->value * $subtotal);
        }

        $tax = ($subtotal - $discount) * $this->taxRate;
        $total = ($subtotal - $discount) + $tax + $this->baseShippingCost;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'coupon' => $coupon,
            'coupon_id' => $couponId,
        ];
    }

    private function updateStock(array $items)
    {
        foreach ($items as $item) {
            if (!empty($item['product_variant_id'])) {
                $variant = ProductVariant::findOrFail($item['product_variant_id']);
                if ($variant->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for variant: {$variant->name}");
                }
                $variant->decrement('quantity', $item['quantity']);
            } else {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                $product->decrement('stock', $item['quantity']);
            }
        }
    }

    private function getValidCoupon(?string $code, float $subtotal)
    {
        if (!$code) return null;

        return Cupon::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_usage')
                    ->orWhereRaw('used_count < max_usage');
            })
            ->first();
    }
}
