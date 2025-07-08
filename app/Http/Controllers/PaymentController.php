<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Filters\PaymentFilter;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\PaymentStatusUpdated;

class PaymentController extends Controller
{
    // send email + generate pdf
    // satim credinals

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

    public function show(Request $request, Payment $payment)
    {
        try {
            //
            $payment->load([
                'user.addresse',
                'order.coupon'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Payment retrieved successfully',
                'data' => new PaymentResource($payment)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request, PaymentService $paymentService)
    {
        try {
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
            ]);
            //
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $validatedData = $validator->validated();
            //
            $payment = $paymentService->processPayment([
                'order_id' => $validatedData['order_id'],
                'user_id' => $user->id,
            ]);
            //
            return response()->json([
                'status' => true,
                'message' => 'Payment recorded successfully',
                'data' => new PaymentResource($payment),
                'payment_url' => $payment['payment_url'],
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

    public function updateStatus(Request $request, Payment $payment)
    {
        DB::beginTransaction();
        try {
            $payment->load([
                'user.addresse',
                'order.coupon'
            ]);
            //
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,completed,failed,refunded',
            ]);
            //
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $payment->status = $request->status;
            $payment->save();
            //
            event(new PaymentStatusUpdated($payment));
            //
            DB::commit();
            //
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

    public function ConfirmePaymentStatus(Request $request, PaymentService $paymentService)
    {
        try {
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|exists:payments,transaction_id',
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
            //
            $confirmatoin = $paymentService->ConfirmePament($data['transaction_id']);
            //
            return response()->json([
                'status' => true,
                'message' => 'status payment succses',
                'data' => $confirmatoin
            ], 500);
            //
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process status payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function failPaymentStatus(Request $request, PaymentService $paymentService)
    {
        try {
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|exists:payments,transaction_id',
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
            //
            $confirmatoin = $paymentService->failPayment($data['transaction_id']);
            //
            return response()->json([
                'status' => true,
                'message' => 'status payment fail get',
                'data' => $confirmatoin
            ], 500);
            //
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to process status payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
