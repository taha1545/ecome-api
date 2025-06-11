<?php

namespace App\Http\Controllers;

use App\Filters\ContactFilter;
use App\Http\Requests\StoreContactRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            // 
            if (Auth::user() && Auth::user()->role === 'admin') {
                $query->with('user');
            }
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


    public function show($id)
    {
        try {
            //
            $user = Auth::user();
            //
            $contact = Contact::when($user && $user->role === 'admin', function ($query) {
                return $query->with('user');
            })->findOrFail($id);
            // 
            if ($user->role !== 'admin' && $contact->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to view this contact'
                ], 403);
            }
            //
            return response()->json([
                'status' => true,
                'message' => 'Contact retrieved successfully',
                'data' => new ContactResource($contact)
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreContactRequest $request)
    {
        try {
            $user = $request->userAuthorized();
            $validatedData = $request->validated();
            //
            if ($user->role !== 'admin') {
                $validatedData['user_id'] = $user->id;
            } else if (!isset($validatedData['user_id'])) {
                $validatedData['user_id'] = $user->id;
            }
            // 
            if (isset($validatedData['is_primary']) && $validatedData['is_primary']) {
                Contact::where('user_id', $validatedData['user_id'])
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            //
            $contact = Contact::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Contact created successfully',
                'data' => new ContactResource($contact)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(StoreContactRequest $request, $id)
    {
        try {
            $user = $request->userAuthorized();
            $contact = Contact::findOrFail($id);
            // 
            if ($user->role !== 'admin' && $contact->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to update this contact'
                ], 403);
            }
            $validatedData = $request->validated();
            // 
            if ($user->role !== 'admin') {
                unset($validatedData['user_id']);
            }
            // 
            if (isset($validatedData['is_primary']) && $validatedData['is_primary'] && !$contact->is_primary) {
                Contact::where('user_id', $contact->user_id)
                    ->where('id', '!=', $contact->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
            // Update the contact
            $contact->update($validatedData);
            //
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


    public function destroy($id)
    {
        try {
            //
            $user = Auth::user();
            $contact = Contact::findOrFail($id);
            //
            if ($user->role !== 'admin' && $contact->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to delete this contact'
                ], 403);
            }
            // 
            $contact->delete();
            //
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
