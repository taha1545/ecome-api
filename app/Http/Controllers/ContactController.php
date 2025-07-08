<?php

namespace App\Http\Controllers;

use App\Filters\ContactFilter;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{

    public function index(Request $request)
    {
        try {
            // 
            $query = Contact::query();
            // 
            $filter = new ContactFilter($request);
            $query = $filter->apply($query);
            $query->with('user');
            //
            $perPage = $request->get('per_page', 15);
            $contacts = $query->paginate($perPage);
            //
            return response()->json([
                'status' => true,
                'message' => 'Contacts retrieved successfully',
                'data' => new ContactCollection($contacts)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve contacts',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Contact $contact)
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'Contact retrieved successfully',
                'data' => new ContactResource($contact),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve contact',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|max:20',
                'notes' => 'required|string|max:1000',
                'type' => 'nullable|string|in:personal,emergency,business',
                'is_primary' => 'sometimes|boolean',
                'user_id' => 'nullable|exists:users,id',
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
            $contact = Contact::create($data);
            //
            return response()->json([
                'status' => true,
                'message' => 'Contact created successfully',
                'data' => new ContactResource($contact)
            ], 201);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Contact $contact)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'sometimes|string|max:20',
                'notes' => 'sometimes|string|max:1000',
                'type' => 'sometimes|string|in:personal,emergency,business',
                'is_primary' => 'sometimes|boolean',
                'user_id' => 'sometimes|exists:users,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            $data = $validator->validated();
            $contact->update($data);
            return response()->json([
                'status' => true,
                'message' => 'Contact updated successfully',
                'data' => new ContactResource($contact)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Contact $contact)
    {
        try {
            $contact->delete();
            return response()->json([
                'status' => true,
                'message' => 'Contact deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
