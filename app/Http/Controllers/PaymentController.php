<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Filters\PaymentFilter;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Http\Requests\StorePaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\PaymentStatusUpdated;

class PaymentController extends Controller
{


    public function index(Request $request)
    {
        try {
            //
            $query = Payment::query();
            //
            $filter = new PaymentFilter($request);
            $query = $filter->apply($query);
            //
            $query->with(['user', 'order']);
            //
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);
            //
            return response()->json([
                'status' => true,
                'message' => 'Payments retrieved successfully',
                'data' => new PaymentCollection($payments)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            //
            $user = $request->user();
            //
            $payment = Payment::with([
                'user.addresse',
                'order.coupon'
            ])->findOrFail($id);
            //
            if ($user->role !== 'admin' && $payment->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to view this payment'
                ], 403);
            }
            //
            return response()->json([
                'status' => true,
                'message' => 'Payment retrieved successfully',
                'data' => new PaymentResource($payment)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(StorePaymentRequest $request, PaymentService $paymentService)
    {
        try {
            $user = $request->userAuthorized();
            $validatedData = $request->validated();
            // 
            $order = Order::findOrFail($validatedData['order_id']);
            //
            if ($user->role !== 'admin' && $order->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to create payment for this order'
                ], 403);
            }
            //
            $payment = $paymentService->processPayment([
                'order_id' => $validatedData['order_id'],
                'user_id' => $user->id,
                'method' => $validatedData['method'],
                'amount' => $validatedData['amount'],
                'currency' => $validatedData['currency'],
                'status' => $validatedData['status'],
                'transaction_id' => $validatedData['transaction_id'] ?? null,
                'gateway_id' => $validatedData['gateway_id'] ?? null,
                'gateway_response' => $validatedData['gateway_response'] ?? null,
                'error_code' => $validatedData['error_code'] ?? null,
                'error_message' => $validatedData['error_message'] ?? null,
                'payment_data' => $validatedData['payment_data'] ?? [],
            ]);
            //
            return response()->json([
                'status' => true,
                'message' => 'Payment recorded successfully',
                'data' => new PaymentResource($payment)
            ], 201);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update payment status and broadcast real-time update
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::with(['user', 'order'])->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,completed,failed,refunded',
                'transaction_id' => 'nullable|string',
                'payment_details' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $oldStatus = $payment->status;
            $payment->status = $request->status;
            
            if ($request->has('transaction_id')) {
                $payment->transaction_id = $request->transaction_id;
            }
            
            if ($request->has('payment_details')) {
                $payment->payment_details = $request->payment_details;
            }

            $payment->save();

            // Broadcast real-time update
            event(new PaymentStatusUpdated($payment));

            // Update order status if payment is completed
            if ($request->status === 'completed' && $oldStatus !== 'completed') {
                $payment->order->update(['status' => 'processing']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment status updated successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment gateway webhooks
     */
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            $signature = $request->header('X-Payment-Signature');

            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, $signature)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid webhook signature'
                ], 400);
            }

            $payment = Payment::where('transaction_id', $payload['transaction_id'])->first();
            
            if (!$payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Update payment status based on webhook
            $payment->status = $this->mapPaymentStatus($payload['status']);
            $payment->payment_details = array_merge($payment->payment_details ?? [], [
                'webhook_data' => $payload,
                'webhook_received_at' => now()
            ]);
            $payment->save();

            // Broadcast real-time update
            event(new PaymentStatusUpdated($payment));

            return response()->json([
                'status' => true,
                'message' => 'Webhook processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time payment status
     */
    public function getPaymentStatus($id)
    {
        try {
            $payment = Payment::with(['user', 'order'])
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Payment status retrieved successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'order' => [
                        'id' => $payment->order->id,
                        'status' => $payment->order->status,
                        'total_amount' => $payment->order->total_amount
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($payload, $signature)
    {
        // Implement your webhook signature verification logic here
        // This is just a placeholder
        return true;
    }

    /**
     * Map payment gateway status to internal status
     */
    private function mapPaymentStatus($gatewayStatus)
    {
        $statusMap = [
            'succeeded' => 'completed',
            'failed' => 'failed',
            'pending' => 'pending',
            'processing' => 'processing',
            'refunded' => 'refunded'
        ];

        return $statusMap[$gatewayStatus] ?? 'pending';
    }

    public function refund(Request $request, $id, PaymentService $paymentService)
    {
        try {
            $user = Request()->user();
            $payment = Payment::findOrFail($id);
            //
            if ($user->role !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to process refund'
                ], 403);
            }
            //
            if ($payment->status !== 'succeeded') {
                return response()->json([
                    'status' => false,
                    'message' => 'Only successful payments can be refunded'
                ], 400);
            }
            // 
            $validator = Validator::make($request->all(), [
                'amount' => "sometimes|numeric|min:0.01|max:{$payment->amount}",
                'reason' => 'sometimes|string|max:255',
                'refund_id' => 'nullable|string',
                'refund_status' => 'required|string|in:succeeded,failed',
                'gateway_response' => 'nullable',
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
            $payment = $paymentService->refundPayment(
                $payment,
                $request->amount,
                $request->reason,
                [
                    'refund_id' => $request->refund_id,
                    'refund_status' => $request->refund_status,
                    'gateway_response' => $request->gateway_response,
                    'processed_at' => $request->processed_at ?? now(),
                ]
            );
            //
            return response()->json([
                'status' => true,
                'message' => 'Payment refund recorded successfully',
                'data' => new PaymentResource($payment)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to record refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
