<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    private $cacheDuration = 3;


    public function summary(Request $request)
    {
        try {
            $totalSales = Order::where('status', 'delivered')->count();
            // 
            $totalOrders = Order::count();
            // 
            $totalCustomers = User::where('role', 'client')->count();
            // 
            $recentOrders = Order::with(['user:id,name,email'])
                ->latest()
                ->take(5)
                ->get();
            //
            return response()->json([
                'status' => true,
                'message' => 'Dashboard summary retrieved successfully',
                'data' => [
                    'total_delivreed_order' => $totalSales,
                    'total_orders' => $totalOrders,
                    'total_customers' => $totalCustomers,
                ]
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function salesAnalytics(Request $request)
    {
        try {

            $period = $request->get('period', 'month');
            // 
            $salesQuery = Order::where('status', 'delivered')
                ->select([
                    DB::raw('COUNT(*) as total_orders '),
                    DB::raw('SUM(total) as total_sales'),
                    DB::raw('AVG(total) as average_order_value')
                ])
                ->when($period === 'day', function ($query) {
                    return $query->whereDate('created_at', today());
                })
                ->when($period === 'week', function ($query) {
                    return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($period === 'month', function ($query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                })
                ->when($period === 'year', function ($query) {
                    return $query->whereYear('created_at', now()->year);
                })
                ->when($period === 'all', function ($query) {
                    return $query;
                });
            // Get top selling products
            $topProducts = Product::query()
                ->select([
                    'products.*',
                    DB::raw('COUNT(order_items.id) as total_orders'),
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
                ])
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'delivered')
                ->when($period === 'day', function ($query) {
                    return $query->whereDate('orders.created_at', today());
                })
                ->when($period === 'week', function ($query) {
                    return $query->whereBetween('orders.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($period === 'month', function ($query) {
                    return $query->whereMonth('orders.created_at', now()->month)
                        ->whereYear('orders.created_at', now()->year);
                })
                ->when($period === 'year', function ($query) {
                    return $query->whereYear('orders.created_at', now()->year);
                })
                ->groupBy('products.id')
                ->orderBy('total_quantity', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Sales analytics retrieved successfully',
                'data' => [
                    'sales_by_period' => $salesQuery->get(),
                    'top_selling_products' => $topProducts
                ],
                'meta' => [
                    'period' => $period
                ]
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve sales analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderAnalytics(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            // 
            $orderStatus = Order::select('status', DB::raw('COUNT(*) as count'))
                ->when($period === 'day', function ($query) {
                    return $query->whereDate('created_at', today());
                })
                ->when($period === 'week', function ($query) {
                    return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($period === 'month', function ($query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                })
                ->when($period === 'year', function ($query) {
                    return $query->whereYear('created_at', now()->year);
                })
                ->when($period === 'all', function ($query) {
                    return $query;
                })
                ->groupBy('status')
                ->get();
            // 
            $averageOrderValue = Order::where('status', 'delivered')
                ->when($period === 'day', function ($query) {
                    return $query->whereDate('created_at', today());
                })
                ->when($period === 'week', function ($query) {
                    return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($period === 'month', function ($query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                })
                ->when($period === 'year', function ($query) {
                    return $query->whereYear('created_at', now()->year);
                })
                ->avg('total');
            // 
            $fulfillmentTime = Order::where('status', 'delivered')
                ->when($period === 'day', function ($query) {
                    return $query->whereDate('created_at', today());
                })
                ->when($period === 'week', function ($query) {
                    return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                })
                ->when($period === 'month', function ($query) {
                    return $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                })
                ->when($period === 'year', function ($query) {
                    return $query->whereYear('created_at', now()->year);
                })
                ->select(DB::raw('AVG(EXTRACT(EPOCH FROM updated_at - created_at) / 3600) as avg_fulfillment_hours'))
                ->first();
            //
            return response()->json([
                'status' => true,
                'message' => 'Order analytics retrieved successfully',
                'data' => [
                    'order_status_distribution' => $orderStatus,
                    'average_order_value' => $averageOrderValue,
                    'average_fulfillment_time_hours' => $fulfillmentTime->avg_fulfillment_hours
                ],
                'meta' => [
                    'period' => $period
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve order analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getOrderNotifications()
    {
        try {
            $notifications = Order::with(['user:id,name,email', 'items.product:id,name'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'total' => $order->total,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'user' => [
                            'id' => $order->user->id,
                            'name' => $order->user->name,
                            'email' => $order->user->email,
                        ],
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->name,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Order notifications retrieved successfully',
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve order notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
