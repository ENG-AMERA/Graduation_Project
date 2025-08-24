<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;

class authservice{
/*
    public function loginadmin($request){

             $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json(['message' => 'invalid info'], 400);
        }

        $user = Auth::user();
        $response = [
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'role'=>$user->roles,
            ]];

        return response()->json($response, 200);
    }*/


    public function login($request)
{
    $credentials = $request->only('email', 'password');
    $jwt = Auth::attempt($credentials);

    if (!$jwt) {
        return response()->json(['message' => 'invalid info'], 400);
    }

    
    $user = \App\Models\User::find(Auth::id());

    // Save the device token if provided
    if ($request->has('device_token')) {
        $user->device_token = $request->input('device_token');
        $user->save();
    }

    return response()->json([
        'user' => $user,
        'authorization' => [
            'token' => $jwt,
            'type' => 'bearer',
            'role' => $user->roles,
        ],
    ], 200);
}

}
