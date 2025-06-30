<?php
namespace App\Http\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Facades\JWTAuth; // Import JWTAuth facade

  // app/Repositories/UserRepository.php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class authrepo{
/*
public function register($request)
{   $userinfo=$request->all();
    $userinfo['password'] = Hash::make($userinfo['password']);
    $user= User::create($userinfo);
    $role=Role::create([
        'user_id'=>$user->id,
        'name'=>'Consumer',
    ]);
     EmailVerificationCode::where('email', $user->email)->delete();

         $code = rand(100000, 999999);

    EmailVerificationCode::create([
        'email' => $user->email,
        'code' => $code,
        'created_at' => now(),
    ]);

    Mail::raw("Your verification code is  $code", function ($message) use ($user) {
        $message->to($user->email)->subject('Verification Code');
    });

    return response()->json([
        'message' => 'User registered successfully , please check your email for verification code',
        'user' => $user,
    ], 201);
}
*/


public function register($request)
{
    // Get all input data
    $userinfo = $request->all();

    // Hash the password before saving it
    $userinfo['password'] = Hash::make($userinfo['password']);

    // Create the user
    $user = User::create($userinfo);

    // Assign a default role to the user
    $role = Role::create([
        'user_id' => $user->id,
        'name' => 'Consumer',
    ]);

    // Delete any existing verification code for the same email
    EmailVerificationCode::where('email', $user->email)->delete();

    // Generate a new random verification code
    $code = rand(100000, 999999);

    // Store the verification code in the database
    EmailVerificationCode::create([
        'email' => $user->email,
        'code' => $code,
        'created_at' => now(),
    ]);

    // // Send the verification code to the user's email
    // Mail::raw("Your verification code is  $code", function ($message) use ($user) {
    //     $message->to($user->email)->subject('Verification Code');
    // });

    // Create a JWT token for the user
    $token = JWTAuth::fromUser($user);

    // Return the response with the token
    return response()->json([
        'message' => 'User registered successfully, please check your email for verification code',
        'user' => $user,
        'token' => $token,  // Send the token in the response
    ], 201);
}

    public function getUserProfileById($id)
    {
        return User::select([
                'firstname',
                'lastname',
                'email',
                'gender',
                'age',
                'phone',
                'location',
                'points',
                'photo' // Optional: can be null
            ])
            ->where('id', $id)
            ->first();
    }

 
public function updateUserPhoto($userId, $photoFile)
{
    if ($photoFile) {
        $fileName = Str::uuid() . '.' . $photoFile->getClientOriginalExtension();

        // Move the file to the public/photos directory
        $photoFile->move(public_path('photos'), $fileName);

        // Save the relative path in the database
        $user = User::findOrFail($userId);
        $user->photo = 'public/photos/' . $fileName;
        $user->save();

        // Return the full URL to access the photo
        return ('public/photos/' . $fileName);
    }

    return null;
}


}
