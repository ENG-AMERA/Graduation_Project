<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\authrepo;
use App\Http\Requests\Loginrequest;
use Illuminate\Foundation\Auth\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\authservice;


class UserController extends Controller
{
    protected $authservice;
    protected $authRepo;

    public function __construct( authservice $authservice , authrepo $authRepo)
    {
       $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->authservice  = $authservice;
        $this->authRepo = $authRepo;
    }

    public function register(RegisterRequest $request) {

         return $this->authRepo->register($request);
    }

    public function login(Loginrequest $request)
    {
    return $this->authservice->login($request);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
