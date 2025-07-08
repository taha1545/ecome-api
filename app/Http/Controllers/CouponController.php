<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\Order;
use App\Filters\CouponFilter;
use App\Http\Resources\CouponResource;
use App\Http\Resources\CouponCollection;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{


    public function index(Request $request)
    {
        try {
            $query = Cupon::query();
            //
            $filter = new CouponFilter($request);
            $query = $filter->apply($query);
            //
            $perPage = $request->get('per_page', 15);
            $coupons = $query->paginate($perPage);
            //
            return response()->json([
                'status' => true,
                'message' => 'Coupons retrieved successfully',
                'data' => new CouponCollection($coupons)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Cupon $coupon)
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Coupon retrieved successfully',
                'data' => new CouponResource($coupon)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:coupons,code',
                'value' => 'required|numeric|min:0',
                'max_usage' => 'required|integer|min:1',
                'expires_at' => 'nullable|date|after:now',
                'is_active' => 'nullable|boolean'
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
            $data['used_count'] = 0;
            $data['is_active'] = $data['is_active'] ?? true;
            //
            $coupon = Cupon::create($data);
            //
            return response()->json([
                'status' => true,
                'message' => 'Coupon created successfully',
                'data' => new CouponResource($coupon)
            ], 201);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Cupon $coupon)
    {
        try {
            $validator = Validator::make($request->all(), [
                'value' => 'sometimes|numeric|min:0',
                'max_usage' => "sometimes|integer",
                'expires_at' => 'sometimes|date',
                'is_active' => 'sometimes|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            $coupon->update($data);
            return response()->json([
                'status' => true,
                'message' => 'Coupon updated successfully',
                'data' => new CouponResource($coupon)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Cupon $coupon)
    {
        try {
            $ordersCount = Order::where('coupon_id', $coupon->id)->count();
            if ($ordersCount > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete coupon as it is used in ' . $ordersCount . ' orders'
                ], 422);
            }
            $coupon->delete();
            return response()->json([
                'status' => true,
                'message' => 'Coupon deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addCouponToOrder(Request $request, Order $order)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            // Validate request
            $validator = Validator::make($request->all(), [
                'coupon_code' => 'required|string|exists:coupons,code'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Get coupon
            $coupon = Cupon::where('code', $request->coupon_code)
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
            if (!$coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon is invalid, expired, or has reached maximum usage'
                ], 422);
            }
            if ($order->coupon_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already has a coupon applied'
                ], 422);
            }
            $order->coupon_id = $coupon->id;
            $discount = ($coupon->value / 100) * $order->subtotal;
            if ($discount > $order->subtotal) {
                $discount = $order->subtotal;
            }
            $order->total = $order->subtotal + $order->tax + $order->shipping_cost - $discount;
            $order->save();
            $coupon->increment('used_count');
            $order->load([
                'user.addresse',
                'items.product.files',
                'items.variant',
                'coupon',
                'payments'
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Coupon applied to order successfully',
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to apply coupon to order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateCoupon(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string'
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
            $coupon = Cupon::where('code', $request->code)
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
            //
            if (!$coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon is invalid, expired, or has reached maximum usage'
                ], 422);
            }
            //
            return response()->json([
                'status' => true,
                'message' => 'Coupon is valid',
                'data' => new CouponResource($coupon)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to validate coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
