<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthMail;
use App\Mail\ResetLinkMail;


class AuthController extends Controller
{
   //Register user
    public function register(Request $request)
    {
       // Validate the request data
       $validatedData = $request->validate([
           'full_name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'role' => 'required|string|exists:roles,name',
           'password' => 'required|string|min:8|confirmed',
           'status' => 'nullable|string'
       ]);


       // Create the user
       $user = User::create([
           'name' => $validatedData['name'],
           'email' => $validatedData['email'],
           'password' => Hash::make($validatedData['password']),
           'status' => $validatedData['status'] ?? 'active',
           'created_by' => auth()->id(), 
       ]);

       //Attach role
        $role = Role::where('name', $validatedData['role'])->first();
        $user-roles()->attach($role->id);   

       // Generate a token for the user
       $token = Password::createToken($user);
       $resetUrl = config('app.frontend_url') . "/reset-password?token={$token}&email={$user->email}";
       $loginUrl = config('app.frontend_url') . "/login";
       $temporaryPassword = $validatedData['password'];

       //Send email
       Mail::to($user->email) ->send(new AuthMail($user, $resetUrl, $loginUrl, $temporaryPassword));


       $authToken = $user->createToken('auth_token')->plainTextToken;


       // Return the user and token
       return response()->json([
           'access_token' => $token,
           'token_type' => 'Bearer',
           'user' => $user,
       ], 201);
    } 

   //Login user
    public function login(Request $request){
         $credentials = $request->validate([
              'email' => 'required|string|email',
              'password' => 'required|string',
         ]);
    
         if (!Auth::attempt($credentials)) {
              return response()->json(['message' => 'Invalid credentials'], 401);
         }
    
         $user = Auth::user();
         $token = $user->createToken('auth_token')->plainTextToken;
    
         return response()->json([
              'access_token' => $token,
              'token_type' => 'Bearer',
              'user' => $user,
         ]);
    }
         //Logout user
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    //Get authenticated user details
    public function user(Request $request)
    {
        $user = $request->user()->load('roles');
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
    $request->validate([
        'email' => 'required|string|email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    // Generate reset token
    $token = Password::createToken($user);

    $resetUrl = config('app.frontend_url')
        . "/reset-password?token={$token}&email={$user->email}";

    // Send reset email
    Mail::to($user->email)->send(
        new ResetLinkMail($user->full_name, $resetUrl)
    );

    return response()->json([
        'message' => 'Password reset link sent to your email'
    ]);
    }

    

    
    public function resetPassword(Request $request)
    {
    $request->validate([
        'email'                 => 'required|string|email',
        'token'                 => 'required|string',
        'password'              => 'required|string|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
    );

    if ($status !== Password::PASSWORD_RESET) {
        return response()->json([
            'message' => 'Invalid or expired reset token'
        ], 400);
    }

    return response()->json([
        'success' => true,
        'message' => 'Password reset successful. You can now log in.'
    ]);
    }


  

    public function changePassword(Request $request)
    {
    $request->validate([
        'current_password'      => 'required|string',
        'new_password'          => 'required|string|min:8|confirmed',
    ]);

    $user = $request->user();

    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'message' => 'Current password is incorrect'
        ], 400);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
        'message' => 'Password changed successfully'
    ]);
    }
}