<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //boot detecter 
    // email verify 

    public function signup(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'profile_image' => 'nullable|image|max:4096',
            ], [
                'profile_image.max' => 'The profile image must not be larger than 4MB.',
            ]);
            //
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'error' => $validator->errors()
                ], 422);
            }
            //  image 
            $imagePath = null;
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_image' => $imagePath,
                'role' => 'client',
            ]);
            // Send welcome email
            Mail::to($user->email)->queue(new WelcomeMail($user));
            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;
            //
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'user' => $user->only(['id', 'name', 'email', 'role', 'profile_image']),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
            //
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            // 
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
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
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
            // 
            $user = Auth::user();
            $user->tokens()->delete();
            //
            $token = $user->createToken('auth_token')->plainTextToken;
            //
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user->only(['id', 'name', 'email', 'role', 'profile_image']),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
            //
        } catch (\Exception $e) {
            //
            DB::rollBack();
            //
            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'error' =>  $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // 
            if (!$request->user()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            // 
            $request->user()->tokens()->delete();
            //
            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out'
            ], 200);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Logout failed. Please try again.'
            ], 500);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            // 
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            // 
            $otp = rand(100000, 999999);
            $expiration = now()->addMinutes(15);
            //
            $user = User::where('email', $request->email)->first();
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => $expiration
            ]);
            // 
            Mail::to($user->email)->queue(new OtpMail($otp));
            //
            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully',
                'expires_at' => $expiration->toDateTimeString()
            ], 200);
            //
        } catch (\Exception $e) {
            if (isset($user)) {
                $user->update([
                    'otp_code' => null,
                    'otp_expires_at' => null
                ]);
            }
            //
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
                'otp' => 'required|string',
                'password' => 'required|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Find user by email
            $user = User::where('email', $request->email)->first();
            // Check OTP 
            if (
                !$user ||
                $user->otp_code !== $request->otp ||
                $user->otp_expires_at < now()
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP'
                ], 401);
            }
            // Update 
            $user->update([
                'password' => Hash::make($request->password),
                'otp_code' => null,
                'otp_expires_at' => null
            ]);
            // 
            $user->tokens()->delete();
            //
            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully'
            ], 200);
            //
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Password reset failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        //
        DB::beginTransaction();
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            //
            if ($request->user()) {
                $user = $request->user();
                $userpassword = $request->user()->getHashedPassword();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'user is not allowed '
                ], 401);
            }
            // Verify current password
            if (!Hash::check($request->current_password, $userpassword)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect'
                ], 403);
            }
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
            // 
            $user->tokens()->delete();
            //
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'
            ], 200);
            //
        } catch (\Exception $e) {
            //
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Password update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMe(Request $request)
    {
        //
        try {
            $user = $request->user();
            //
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'user not found'
                ], 404);
            }
            //
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->profile_image
                    ? Storage::url($user->profile_image)
                    : null,
                'created_at' => $user->created_at->toISOString(),
            ];
            //
            return response()->json([
                'status' => true,
                'message' => 'User details retrieved successfully',
                'data' => $userData
            ], 200);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserById(Request $request, $id)
    {
        try {
            //
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|exists:users,id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            //
            $user = User::findOrFail($id);
            // 
            return response()->json([
                'status' => true,
                'message' => 'User retrieved successfully',
                'user' => $user->only(['id', 'name', 'email', 'role', 'profile_image'])
            ], 200);
            //
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateMe(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            //
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'profile_image' => 'nullable|string',
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
            $updateData = $validator->validated();
            // 
            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $updateData['profile_image'] = $request->file('profile_image')
                    ->store('profile_images', 'public');
            } elseif (!empty($updateData['profile_image']) && preg_match('/^data:image\/(\w+);base64,/', $updateData['profile_image'], $type)) {
                $image = substr($updateData['profile_image'], strpos($updateData['profile_image'], ',') + 1);
                $image = base64_decode($image);
                //
                if ($image === false) {
                    throw new \Exception('Base64 decode failed');
                }
                //
                $extension = strtolower($type[1]);
                $filename = 'profile_images/' . uniqid() . '.' . $extension;
                //
                Storage::disk('public')->put($filename, $image);
                //
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                //
                $updateData['profile_image'] = $filename;
                //
            }
            //
            $user->update($updateData);
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->only(['id', 'name', 'email', 'role', 'profile_image'])
            ], 200);
            //
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser(Request $request, $id = null)
    {
        DB::beginTransaction();
        try {
            //
            $authUser = $request->user();
            $isAdmin = $authUser->role === 'admin';
            //
            if ($isAdmin && $id) {
                // 
                $validator = Validator::make(['id' => $id], [
                    'id' => 'required|integer|exists:users,id',
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
                $userToDelete = User::findOrFail($id);
                // 
                if ($userToDelete->id === $authUser->id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Admins must use the self-deletion endpoint'
                    ], 403);
                }
            }
            // Perform deletion
            $userToDelete->tokens()->delete();
            $userToDelete->delete();
            //
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'Account deletion'
            ], 200);
            //
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Account deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function googleLogin(Request $request)
    {
        try {
            // 
            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
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
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);
            //
            if (!$payload) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Google token'
                ], 401);
            }
            //
            $email = $payload['email'];
            $name = $payload['name'] ?? explode('@', $email)[0];
            //
            DB::beginTransaction();
            // 
            $user = User::where('email', $email)->first();
            //
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'client',
                ]);
                // 
                Mail::to($user->email)->queue(new WelcomeMail($user));
            }
            // 
            $user->tokens()->delete();
            // 
            $token = $user->createToken('google-auth')->plainTextToken;
            //
            DB::commit();
            //
            return response()->json([
                'status' => true,
                'message' => 'Google authentication successful',
                'user' => $user->only(['id', 'name', 'email', 'role', 'profile_image']),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
            //
        } catch (Exception $e) {
            DB::rollBack();
            //
            return response()->json([
                'status' => false,
                'message' => 'Google authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
