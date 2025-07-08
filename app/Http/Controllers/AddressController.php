<?php

namespace App\Http\Controllers;

use App\Models\Addresse;
use App\Http\Resources\AddressResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{

    public function show(Request $request, Addresse $address)
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Address retrieved successfully',
                'data' => new AddressResource($address)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rdd = null;
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
                'phone' => 'nullable|string|max:20',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
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
            $data['user_id'] = $user->id;
            //
            $addresses = Addresse::where('user_id', $user->id)->get();
            if ($addresses == []) {
                throw new Exception("user alreadsy has address");
                $rdd = 1;
            }
            //
            $address = Addresse::create($data);
            //
            return response()->json([
                'status' => true,
                'message' => 'Address created successfully',
                'data' => new AddressResource($address)
            ], 201);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create address',
                'error' => $e->getMessage()
            ], isset($rrd) ? 500 : 400);
        }
    }


    public function update(Request $request, Addresse $address)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address_line1' => 'sometimes|string|max:255',
                'address_line2' => 'sometimes|string|max:255',
                'city'          => 'sometimes|string|max:100',
                'postal_code'   => 'sometimes|string|max:20',
                'phone'         => 'sometimes|string|max:20',
                'latitude'      => 'sometimes|numeric',
                'longitude'     => 'sometimes|numeric',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            $address->update($data);
            return response()->json([
                'status' => true,
                'message' => 'Address updated successfully',
                'data' => new AddressResource($address)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $user = $request->user();
            Addresse::where('user_id', $user->id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserAddresses(Request $request)
    {
        try {
            //
            $user = $request->user();
            $addresses = Addresse::where('user_id', $user->id)->get();
            //
            return response()->json([
                'status' => true,
                'message' => 'User addresses retrieved successfully',
                'data' => AddressResource::collection($addresses)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve user addresses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
