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
    private $cacheDuration = 3600; // 1 hour cache

    /**
     * Get overall dashboard summary
     */
    public function summary(Request $request)
    {
        try {
            $cacheKey = 'admin_summary_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () {
                // Get total sales
                $totalSales = Order::where('status', 'completed')
                    ->sum('total_amount');

                // Get total orders
                $totalOrders = Order::count();

                // Get total customers
                $totalCustomers = User::where('role', 'customer')->count();

                // Get recent activities (last 5 orders)
                $recentOrders = Order::with(['user:id,name,email'])
                    ->latest()
                    ->take(5)
                    ->get();

                // Get low stock products count
                $lowStockProducts = Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COALESCE(SUM(variants.quantity), 0) as total_stock')
                    ])
                    ->leftJoin('variants', 'products.id', '=', 'variants.product_id')
                    ->groupBy('products.id')
                    ->having('total_stock', '<=', 10)
                    ->having('total_stock', '>', 0)
                    ->count();

                return response()->json([
                    'status' => true,
                    'message' => 'Dashboard summary retrieved successfully',
                    'data' => [
                        'total_sales' => $totalSales,
                        'total_orders' => $totalOrders,
                        'total_customers' => $totalCustomers,
                        'low_stock_products' => $lowStockProducts,
                        'recent_orders' => $recentOrders
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales analytics
     */
    public function salesAnalytics(Request $request)
    {
        try {
            $cacheKey = 'sales_analytics_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $period = $request->get('period', 'month'); // day, week, month, year
                
                // Get sales by period
                $salesQuery = Order::where('status', 'completed')
                    ->select([
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(total_amount) as total_sales'),
                        DB::raw('AVG(total_amount) as average_order_value')
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
                    ->groupBy('date')
                    ->orderBy('date');

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
                    ->where('orders.status', 'completed')
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
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve sales analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order analytics
     */
    public function orderAnalytics(Request $request)
    {
        try {
            $cacheKey = 'order_analytics_' . md5(json_encode($request->all()));
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($request) {
                $period = $request->get('period', 'month'); // day, week, month, year
                
                // Get order status distribution
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
                    ->groupBy('status')
                    ->get();

                // Get average order value
                $averageOrderValue = Order::where('status', 'completed')
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
                    ->avg('total_amount');

                // Get order fulfillment time
                $fulfillmentTime = Order::where('status', 'completed')
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
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_fulfillment_hours'))
                    ->first();

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
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve order analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time order notifications
     */
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
                        'total_amount' => $order->total_amount,
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