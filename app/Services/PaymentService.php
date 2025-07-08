<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{

    private $SatimServices;

    //

    public function __construct()
    {
        $this->SatimServices = new SatimService();
    }


    public function processPayment(array $data)
    {
        DB::beginTransaction();
        //
        try {
            $order = Order::findOrFail($data['order_id']);
            //
            $OldPayment = Payment::where('order_id', $order->id)->where('status', 'succeeded')->count();
            if ($OldPayment > 0) {
                throw new Exception('payment alredy succsed');
            }
            //
            $orderNumber = (int) (substr(time(), -5) . rand(10000, 99999));
            // call satim
            $satimPayment = $this->SatimServices->registerPayment($order->total, $orderNumber, $data['user_id'] ?? $order->user_id);
            // 
            $payment = new Payment([
                'order_id' => $order->id,
                'user_id' => $data['user_id'] ?? $order->user_id,
                'amount' =>  $order->total,
                'status' => 'pending',
                'transaction_id' => $satimPayment['order_id'] ?? null,
                'order_number' => $orderNumber,
            ]);
            //
            $payment->save();
            DB::commit();
            // and url of satim 
            $payment['payment_url'] = $satimPayment['payment_url'];
            //
            return $payment;
            //
        } catch (\Exception $e) {
            DB::rollBack();
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

            if (isset($data['gateway_response'])) {
                $payment->gateway_response = $data['gateway_response'];
            }

            if (in_array($status, ['succeeded', 'failed', 'refunded']) && !$payment->processed_at) {
                $payment->processed_at = $data['processed_at'] ?? now();
            }
            $payment->save();
            // 
            $order = $payment->order;
            //
            if ($status === 'succeeded' && $oldStatus !== 'succeeded') {
                $order->status = 'processing';
                $order->save();
            } elseif ($status === 'refunded' && $oldStatus !== 'refunded') {
                $order->status = 'canceled';
                $order->save();
            }
            //
            DB::commit();
            return $payment;
            //
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


    public function ConfirmePament(string $OrderId)
    {
        try {
            //
            $Payment = Payment::where('transaction_id', $OrderId)->first();
            if (!$Payment) {
                throw new Exception("payment with transaction_id is not found");
            }
            //
            $satimPayment = $this->SatimServices->getPaymentStatus($OrderId);
            //generate recu
             
            //
            if ($Payment['status'] == 'pending') {
                //
                $Payment->update(attributes: [
                    'desc' => $satimPayment,
                    'processed_at' => now(),
                    'status' => 'succeeded'
                ]);
            }
            //
            return $Payment;
            //
        } catch (Exception $e) {
            throw new Exception(" can't get status of payment ");
        }
    }

    public function failPayment(string $OrderId)
    {
        try {
            //
            $Payment = Payment::where('transaction_id', $OrderId)->first();
            if (!$Payment) {
                throw new Exception("payment with transaction_id is not found");
            }
            //
            $satimPayment = $this->SatimServices->getPaymentStatus($OrderId);
            //
            if ($Payment['status'] == 'pending') {
                //
                $Payment->update(attributes: [
                    'error_message' => $satimPayment,
                    'processed_at' => now(),
                    'status' => 'failed'
                ]);
            }
            //
            return $Payment;
            //
        } catch (Exception $e) {
            throw new Exception(" can't get status of payment ");
        }
    }
}
