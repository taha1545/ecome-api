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
    // usage coupon by user
    //test
   
    public function index(Request $request)
    {
        try {
            $query = Cupon::query();
            
            $filter = new CouponFilter($request);
            $query = $filter->apply($query);
            
            $perPage = $request->get('per_page', 15);
            $coupons = $query->paginate($perPage);
            
            return response()->json([
                'status' => true,
                'message' => 'Coupons retrieved successfully',
                'data' => new CouponCollection($coupons)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $coupon = Cupon::findOrFail($id);
            
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
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only admins can create coupons.'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:coupons,code',
                'value' => 'required|numeric|min:0',
                'max_usage' => 'required|integer|min:1',
                'expires_at' => 'nullable|date|after:now',
                'is_active' => 'nullable|boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $validator->validated();
            $data['used_count'] = 0;
            $data['is_active'] = $data['is_active'] ?? true;
            
            $coupon = Cupon::create($data);
            
            return response()->json([
                'status' => true,
                'message' => 'Coupon created successfully',
                'data' => new CouponResource($coupon)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only admins can update coupons.'
                ], 403);
            }
            
            $coupon = Cupon::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'code' => 'nullable|string|max:50|unique:coupons,code,' . $id,
                'value' => 'nullable|numeric|min:0',
                'max_usage' => 'nullable|integer|min:' . $coupon->used_count,
                'expires_at' => 'nullable|date',
                'is_active' => 'nullable|boolean'
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

   
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only admins can delete coupons.'
                ], 403);
            }
            
            $coupon = Cupon::findOrFail($id);
            
            // Check if coupon is used in any orders
            $ordersCount = Order::where('coupon_id', $id)->count();
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

    public function addCouponToOrder(Request $request, $orderId)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            
            $order = Order::findOrFail($orderId);
            
            // 
            if (!$user || ($user->role !== 'admin' && $order->user_id !== $user->id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this order'
                ], 403);
            }
            
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
                ->where(function($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->where(function($query) {
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
            
            // Check if order already has a coupon
            if ($order->coupon_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already has a coupon applied'
                ], 422);
            }
            
            // Apply coupon to order
            $order->coupon_id = $coupon->id;
            
            // Recalculate order total
            $discount = $coupon->value;
            if ($discount > $order->subtotal) {
                $discount = $order->subtotal;
            }
            
            $order->total = $order->subtotal + $order->tax + $order->shipping_cost - $discount;
            $order->save();
            
            // Increment coupon usage
            $coupon->increment('used_count');
            
            // Load relationships for the resource
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
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $coupon = Cupon::where('code', $request->code)
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->where(function($query) {
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
