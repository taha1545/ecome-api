<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{

    public function processPayment(array $data)
    {
        DB::beginTransaction();

        try {
            // 
            $order = Order::findOrFail($data['order_id']);
            // 
            $payment = new Payment([
                'order_id' => $order->id,
                'user_id' => $data['user_id'] ?? $order->user_id,
                'amount' => $data['amount'] ?? $order->total,
                'currency' => $data['currency'] ?? 'USD',
                'method' => $data['method'],
                'status' => $data['status'] ?? 'pending',
                'transaction_id' => $data['transaction_id'] ?? null,
                'gateway_id' => $data['gateway_id'] ?? null,
                'gateway_response' => isset($data['gateway_response']) ? json_encode($data['gateway_response']) : null,
                'error_code' => $data['error_code'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'processed_at' => $data['processed_at'] ?? ($data['status'] === 'succeeded' ? now() : null),
            ]);
            //
            $payment->save();
            // 
            if ($payment->status === 'succeeded') {
                $order->status = 'processing';
                $order->save();
            } elseif ($payment->status === 'failed') {
                $order->status = 'pending';
                $order->save();
            }
            //
            DB::commit();
            //
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment recording failed: ' . $e->getMessage(), [
                'order_id' => $data['order_id'] ?? null,
                'method' => $data['method'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function updatePaymentStatus(Payment $payment, string $status, array $data = [])
    {
        DB::beginTransaction();

        try {
            $oldStatus = $payment->status;
            $payment->status = $status;
            // 
            if (isset($data['transaction_id'])) {
                $payment->transaction_id = $data['transaction_id'];
            }

            if (isset($data['gateway_id'])) {
                $payment->gateway_id = $data['gateway_id'];
            }

            if (isset($data['error_code'])) {
                $payment->error_code = $data['error_code'];
            }

            if (isset($data['error_message'])) {
                $payment->error_message = $data['error_message'];
            }

            if (isset($data['gateway_response'])) {
                $payment->gateway_response = $data['gateway_response'];
            }

            if (in_array($status, ['succeeded', 'failed', 'refunded']) && !$payment->processed_at) {
                $payment->processed_at = $data['processed_at'] ?? now();
            }

            $payment->save();

            // 
            $order = $payment->order;

            if ($status === 'succeeded' && $oldStatus !== 'succeeded') {
                $order->status = 'processing';
                $order->save();
            } elseif ($status === 'refunded' && $oldStatus !== 'refunded') {
                $order->status = 'canceled';
                $order->save();
            }

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment status update failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus ?? null,
                'new_status' => $status,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function refundPayment(Payment $payment, ?float $amount = null, ?string $reason = null, array $refundData = [])
    {
        DB::beginTransaction();

        try {
            // Check if payment can be refunded
            if ($payment->status !== 'succeeded') {
                throw new \Exception('Only successful payments can be refunded');
            }

            // Default to full refund if amount not specified
            $refundAmount = $amount ?? $payment->amount;

            // Update payment with refund information
            $payment->status = 'refunded';
            $payment->processed_at = $refundData['processed_at'] ?? now();

            // Store refund details in gateway_response
            $refundDetails = [
                'refund_id' => $refundData['refund_id'] ?? 'ref_' . uniqid(),
                'amount' => $refundAmount,
                'reason' => $reason ?? 'Customer requested',
                'processed_at' => now()->toIso8601String(),
            ];

            // If we have existing gateway response, merge with it
            if ($payment->gateway_response) {
                $existingResponse = json_decode($payment->gateway_response, true) ?? [];
                $refundDetails = array_merge($existingResponse, ['refund' => $refundDetails]);
            }

            $payment->gateway_response = json_encode($refundDetails);
            $payment->save();

            // Update order status
            $order = $payment->order;
            $order->status = 'canceled';
            $order->save();

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment refund recording failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'reason' => $reason,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
