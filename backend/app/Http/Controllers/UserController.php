<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ContactUs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\FlareClient\FlareMiddleware\CensorRequestBodyFields;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|min:2',
                'last_name' => 'required|string|min:2',
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'required|string|min:11|max:11|unique:users,phone_number',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|same:password'
            ], [
                'first_name.required' => 'First name is required.',
                'first_name.min' => 'First name must be at least 2 characters.',
                'last_name.required' => 'Last name is required.',
                'last_name.min' => 'Last name must be at least 2 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered.',
                'phone_number.required' => 'Phone number is required.',
                'phone_number.min' => 'Phone number must be exactly 11 digits.',
                'phone_number.max' => 'Phone number must be exactly 11 digits.',
                'phone_number.unique' => 'This phone number is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 6 characters.',
                'confirm_password.required' => 'Please confirm your password.',
                'confirm_password.same' => 'Password confirmation does not match.'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response()->json([
                    'message' => $errors,
                    'status' => 400
                ], 400);
            }

            $userData = $request->all();
            $userData['password'] = bcrypt($userData['password']);
            $userData['role'] = 'user';
            $userData['avatar'] = 'images/DefaultProfile/defaultAvatar.png';
            
            $user = User::create($userData);

            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'avatar' => $user->avatar,
                    'token' => $token,
                    'message' => 'Registration successful',
                    'status' => 200
                ], 200);
            }

            return response()->json([
                'message' => ['Failed to create user account'],
                'status' => 400
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => ['An error occurred during registration: ' . $e->getMessage()],
                'status' => 500
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json([
                'message' => 'This account does not exist. Please register first.',
                'status' => 401
            ], 401);
        }

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid password. Please try again.',
                'status' => 401
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'message' => 'Login successful',
            'token' => $token,
            'status' => 200,
        ], 200);
    }

    public function changeProfile(Request $request)
    {
        try {
            $user = User::find($request->id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'status' => 404
                ], 404);
            }

            $validator = validator($request->all(), [
                "avatar" => "required|image|mimes:jpeg,png,jpg,webp|max:5120",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'status' => 422
                ], 422);
            }

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(32) . '.' . $extension;
                
                // Store using the new storage approach
                $path = $file->storeAs('avatars', $filename, 'local');
                
                if (!$path) {
                    throw new \Exception('Failed to store avatar');
                }

                // Delete old avatar if exists and it's not the default
                if ($user->avatar && $user->avatar !== 'images/DefaultProfile/defaultAvatar.png' && Storage::exists($user->avatar)) {
                    Storage::delete($user->avatar);
                }

                $user->update([
                    'avatar' => $path
                ]);

                return response()->json([
                    'message' => 'Avatar successfully updated!',
                    'status' => 200,
                    'data' => $user,
                    'avatar_url' => env('VITE_STORAGE_URL', env('APP_URL')) . '/api/storage/' . $path
                ], 200);
            }

            return response()->json([
                'message' => 'No avatar file provided',
                'status' => 400
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update avatar: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function contactUs(Request $request)
    {
        $contactUsData['first_name'] = $request->first_name;
        $contactUsData['last_name'] = $request->last_name;
        $contactUsData['email'] = $request->email;
        $contactUsData['phone'] = $request->phone;
        $contactUsData['event_type'] = $request->event_type;
        $contactUsData['message'] = $request->message;

        $contactUs = ContactUs::create($contactUsData);

        if ($contactUs) {
            return response()->json([
                'message' => 'Contact us successful',
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Contact us not successful',
                'status' => 400,
            ], 400);
        }
    }

    public function getAllContactUs()
    {
        $contactUs = ContactUs::all();
        return response()->json($contactUs);
    }

    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function getUserById($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

    public function getMyInfo()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'User info retrieved successfully',
            'status' => 200,
            'data' => $user,
        ], 200);
    }

    public function blockUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => 404
            ], 404);
        }

        $user->status = 'inactive';
        $result = $user->save();

        if ($result) {
            return response()->json([
                'message' => 'User blocked successfully',
                'status' => 200,
                'data' => $result,
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not blocked',
                'status' => 400,
            ], 400);
        }
    }

    public function updateUserInfo(Request $request)
    {
        $user = User::find($request->id);

        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->phone_number = $request->phone;
        $user->role = $request->role;
        $user->status = $request->status;
        $user->flag = $request->flag;

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => 404
            ], 404);
        } else {
            return response()->json([
                'message' => 'User updated successfully',
                'status' => 200
            ], 200);
        }
    }

    public function addUser(Request $request)
    {
        try {
            $userData = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role' => 'required|string|in:admin,staff,user',
                'phone_number' => 'required|regex:/^[0-9]{11}$/|size:11',
            ]);

            $userData['password'] = bcrypt($userData['password']);
            $userData['status'] = 'active';
            $userData['flag'] = '0';

            $user = User::create($userData);

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function updateUserPassword(Request $request)
    {
        // ... existing code ...
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.exists' => 'This email is not registered in our system.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 400
                ], 400);
            }

            $user = User::where('email', $request->email)->first();
            $resetToken = Str::random(60);
            $user->password_reset_token = $resetToken;
            $user->save();

            // Generate reset URL
            $resetUrl = config('app.frontend_url', 'http://localhost:5173') . '/reset-password?token=' . $resetToken;

            // Send reset email
            Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));

            return response()->json([
                'message' => 'Password reset link has been sent to your email',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
