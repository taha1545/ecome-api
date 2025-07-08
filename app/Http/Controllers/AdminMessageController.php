<?php

namespace App\Http\Controllers;

use App\Mail\AdminMessageMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminMessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            //
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'subject' => 'required|string|max:500',
                'message' => 'required|string',
            ]);
            //
            $user = User::findOrFail($validated['user_id']);
            Mail::to($user->email)->send(new AdminMessageMail($user, $validated['subject'], $validated['message']));
            //
            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully.'
            ]);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
