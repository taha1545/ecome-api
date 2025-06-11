<?php

namespace App\Services;

use App\Models\Addresse;
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
        $this->baseShippingCost = Config::get('order.base_shipping_cost', 10.00);
    }


    public function createOrder(array $data)
    {
        //
        DB::beginTransaction();
        //
        try {
            // 
            $user = User::findOrFail($data['user_id']);
            // 
            if (isset($data['shipping_address_id'])) {
                $address = Addresse::where('id', $data['shipping_address_id'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();
            } else {
                // 
                $address = $user->addresse;
                //
                if (!$address) {
                    throw new \Exception('User does not have a shipping address');
                }
            }
            // 
            $orderDetails = $this->calculateOrderDetails($data);
            //
            $order = Order::create([
                'user_id' => $data['user_id'],
                'coupon_id' => $orderDetails['coupon_id'],
                'status' => $data['status'] ?? 'pending',
                'subtotal' => $orderDetails['subtotal'],
                'tax' => $orderDetails['tax'],
                'shipping_cost' => $orderDetails['shipping_cost'],
                'total' => $orderDetails['total'],
                'notes' => $data['notes'] ?? null,
            ]);
            // 
            foreach ($orderDetails['items'] as $item) {
                $order->items()->create($item);
            }
            // 
            if ($orderDetails['coupon']) {
                $orderDetails['coupon']->increment('used_count');
            }
            //
            DB::commit();
            //
            return $order;
            //
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateOrderDetails(array $data)
    {
        //
        $subtotal = 0;
        $orderItems = [];
        $coupon = null;
        $couponId = null;
        $discount = 0;
        $itemCount = 0;
        $totalWeight = 0;
        //
        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemCount += $item['quantity'];
            //
            if (isset($item['product_variant_id']) && $item['product_variant_id']) {
                $variant = ProductVariant::findOrFail($item['product_variant_id']);
                //
                if ($variant->product_id != $product->id) {
                    throw new \Exception("Product variant does not belong to the product");
                }
                //
                $unitPrice = $variant->price;
                $weight = $variant->weight ?? $product->weight ?? 0;
                //
                if (property_exists($variant, 'quantity') && $variant->quantity < $item['quantity']) {
                    throw new \Exception("Product variant '{$product->name} - {$variant->name}' has insufficient quantity");
                }
            } else {
                $unitPrice = $product->discount_price ?? $product->price;
                $weight = $product->weight ?? 0;
                // 
                if (property_exists($product, 'stock') && $product->stock < $item['quantity']) {
                    throw new \Exception("Product '{$product->name}' has insufficient quantity");
                }
            }
            //
            $itemTotal = $unitPrice * $item['quantity'];
            $subtotal += $itemTotal;
            $totalWeight += $weight * $item['quantity'];

            $orderItems[] = [
                'product_id' => $product->id,
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'unit_price' => $unitPrice,
                'quantity' => $item['quantity'],
                'discount_amount' => 0,
            ];
        }

        //
        if (isset($data['coupon_code']) && $data['coupon_code']) {
            $coupon = $this->validateAndGetCoupon($data['coupon_code']);

            if ($coupon) {
                $couponId = $coupon->id;
                $discount = $coupon->value;
                //
                if ($discount > $subtotal) {
                    $discount = $subtotal;
                }
            }
        }
        //
        $tax = $subtotal * $this->taxRate;
        // 
        $shippingCost = $this->calculateShippingCost($totalWeight, $itemCount, $subtotal);
        // 
        $total = $subtotal + $tax + $shippingCost - $discount;
        //
        return [
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'coupon' => $coupon,
            'coupon_id' => $couponId,
            'discount' => $discount,
            'total' => $total,
        ];
    }


    private function calculateShippingCost(float $totalWeight, int $itemCount, float $subtotal): float
    {
        // 
        $shippingCost = $this->baseShippingCost;
        // 
        if ($totalWeight > 5) {
            $shippingCost += ($totalWeight - 5) * 2;
        }
        // 
        if ($itemCount > 10) {
            $shippingCost += ($itemCount - 10) * 1;
        }
        //
        $freeShippingThreshold = Config::get('order.free_shipping_threshold', 100);
        if ($subtotal >= $freeShippingThreshold) {
            $shippingCost = 0;
        }

        return $shippingCost;
    }


    private function validateAndGetCoupon(string $couponCode)
    {
        return Cupon::where('code', $couponCode)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('max_usage')
                    ->orWhere('used_count', '<', DB::raw('max_usage'));
            })
            ->first();
    }
}
