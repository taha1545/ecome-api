<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Order Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the order system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | The default tax rate applied to orders as a decimal (e.g., 0.05 for 5%).
    |
    */
    'tax_rate' => env('ORDER_TAX_RATE', 0.05),

    /*
    |--------------------------------------------------------------------------
    | Base Shipping Cost
    |--------------------------------------------------------------------------
    |
    | The base shipping cost applied to all orders before any adjustments.
    |
    */
    'base_shipping_cost' => env('ORDER_BASE_SHIPPING_COST', 10.00),

    /*
    |--------------------------------------------------------------------------
    | Free Shipping Threshold
    |--------------------------------------------------------------------------
    |
    | Orders with subtotals above this amount qualify for free shipping.
    |
    */
    'free_shipping_threshold' => env('ORDER_FREE_SHIPPING_THRESHOLD', 100.00),
];
