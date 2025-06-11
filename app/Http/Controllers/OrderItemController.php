<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Resources\OrderItemResource;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{

    public function index(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            $order = Order::findOrFail($orderId);
            // 
            if ($user->role !== 'admin' && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to view items for this order'
                ], 403);
            }

            $items = OrderItem::where('order_id', $orderId)
                ->with(['product.files', 'variant'])
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Order items retrieved successfully',
                'data' => OrderItemResource::collection($items)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve order items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $item = OrderItem::with(['product.files', 'variant', 'order'])
                ->findOrFail($id);

            //
            if ($user->role !== 'admin' && $item->order->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to view this order item'
                ], 403);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order item retrieved successfully',
                'data' => new OrderItemResource($item)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve order item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
