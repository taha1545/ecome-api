<?php

namespace App\Http\Controllers;

use App\Models\Addresse;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{

    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $address = Addresse::findOrFail($id);

            // 
            if ($user->role !== 'admin' && $address->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to view this address'
                ], 403);
            }

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
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
                'phone' => 'nullable|string|max:20',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = $user->id;

            $address = Addresse::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Address created successfully',
                'data' => new AddressResource($address)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create address',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $address = Addresse::findOrFail($id);

            //
            if ($user->role !== 'admin' && $address->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this address'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
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


    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $address = Addresse::findOrFail($id);

            // 
            if ($user->role !== 'admin' && $address->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete this address'
                ], 403);
            }

            $address->delete();

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
            $user = $request->user();
            $addresses = Addresse::where('user_id', $user->id)->get();

            return response()->json([
                'status' => true,
                'message' => 'User addresses retrieved successfully',
                'data' => AddressResource::collection($addresses)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve user addresses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
