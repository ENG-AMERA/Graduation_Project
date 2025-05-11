<?php
namespace App\Http\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Registered;

class authrepo{

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

}
