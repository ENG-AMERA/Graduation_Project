<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\Auth;

class authservice{

    public function login($request){

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
            ]];

        return response()->json($response, 200);
    }



}
