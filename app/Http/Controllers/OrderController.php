<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Filters\OrderFilter;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Events\OrderStatusUpdated;
use App\Events\NewOrderPlaced;

class OrderController extends Controller
{

    // 

    public function index(Request $request)
    {
        try {
            //
            $query = Order::query();
            //
            $filter = new OrderFilter($request);
            $query = $filter->apply($query);
            //
            $query->with(['user:id,name,email,profile_image'])
                ->withCount('items');
            //
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);
            //
            return response()->json([
                'status' => true,
                'message' => 'Orders retrieved successfully',
                'data' => new OrderCollection($orders)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, Order $order)
    {
        try {
            $order->load([
                'user.addresse',
                'items.product.files',
                'items.variant',
                'coupon',
                'payments'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Order retrieved successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request, OrderService $orderService)
    {
        try {
            $user = $request->user();
            $request['user_id'] = $user->id;
            //
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1',
                'coupon_code' => 'nullable|string|exists:coupons,code',
            ]);
            //
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            //
            $order = $orderService->createOrder($validator->validated());
            //
            $order->load([
                'user.addresse',
                'items.product.files',
                'items.variant',
                'coupon',
                'payments'
            ]);
            //
            event(new NewOrderPlaced($order));

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order)
            ], 201);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            //
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|string|in:' . implode(',', Order::STATUSES),
                'tax' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0'
            ]);
            //
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            //
            $data = $validator->validated();
            $updated = false;
            //
            if (isset($data['status'])) {
                $oldStatus = $order->status;
                $newStatus = $data['status'];
                $order->status = $newStatus;
                //
                if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
                    $order->cancelled_at = now();
                } elseif ($newStatus !== 'canceled') {
                    $order->cancelled_at = null;
                }
                //
                $updated = true;
                //
            }
            //
            if (isset($data['tax'])) {
                $order->tax = $data['tax'];
                $updated = true;
            }
            //
            if (isset($data['shipping_cost'])) {
                $order->shipping_cost = $data['shipping_cost'];
                $updated = true;
            }
            //
            if ($updated) {
                // 
                $order->total = $order->subtotal + $order->tax + $order->shipping_cost;
                // 
                if ($order->coupon_id) {
                    $coupon = $order->coupon;
                    if ($coupon && $coupon->value) {
                        $discount = $coupon->value;
                        if ($discount > $order->subtotal) {
                            $discount = $order->subtotal;
                        }
                        $order->total -= $discount;
                    }
                }
                //
                $order->save();
                //
                $order->load([
                    'user.addresse',
                    'items.product.files',
                    'items.variant',
                    'coupon',
                    'payments'
                ]);
                //
                DB::commit();
                //
                return response()->json([
                    'status' => true,
                    'message' => 'Order updated successfully',
                    'data' => new OrderResource($order)
                ]);
            }
            //
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'No changes were made to the order'
            ], 422);
            //
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:' . implode(',', Order::STATUSES),
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $oldStatus = $order->status;
            $newStatus = $request->status;
            $order->status = $newStatus;
            if ($newStatus === 'canceled' && $oldStatus !== 'canceled') {
                $order->cancelled_at = now();
            } elseif ($newStatus !== 'canceled') {
                $order->cancelled_at = null;
            }
            $order->save();
            event(new OrderStatusUpdated($order));
            DB::commit();
            $order->load([
                'user.addresse',
                'items.product.files',
                'items.variant',
                'coupon',
                'payments'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to cancel this order'
                ], 403);
            }
            $allowedStatuses = ['pending', 'processing'];
            if (!in_array($order->status, $allowedStatuses)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order cannot be canceled in its current status'
                ], 400);
            }
            $order->status = 'canceled';
            $order->cancelled_at = now();
            $order->save();
            DB::commit();
            $order->load([
                'user.addresse',
                'items.product.files',
                'items.variant',
                'coupon',
                'payments'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Order canceled successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
