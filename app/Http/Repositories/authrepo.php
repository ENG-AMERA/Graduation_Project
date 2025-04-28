<?php
namespace App\Http\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class authrepo{

public function register($request)
{   $userinfo=$request->all();
    $userinfo['password'] = Hash::make($userinfo['password']);
    $user= User::create($userinfo);
    $role=Role::create([
        'user_id'=>$user->id,
        'name'=>'Consumer',
    ]);

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
    ], 201);
}

}
