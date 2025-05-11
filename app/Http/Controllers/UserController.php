<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ResetCodePassword;
use App\Http\Services\authservice;
use App\Http\Repositories\authrepo;
use App\Http\Requests\Loginrequest;
use App\Mail\SendCodeResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationCode;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class UserController extends Controller
{
    protected $authservice;
    protected $authRepo;

    public function __construct( authservice $authservice , authrepo $authRepo)
    {
       $this->middleware('auth:api', ['except' => ['login', 'register','userforgotpassword','userCheckcode','userResetPassword','verifyEmailCode','resendVerificationCode']]);
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

    public function userforgotpassword(Request $request) {
        $data =$request->validate([
            'email'=> 'required|exists:users'
        ]);

       $existing = ResetCodePassword::query()->firstWhere('email', $request['email']);
        if ($existing) {
           $existing->delete();
         }
        $data['code']=mt_rand(100000,999999);
        $codeData=ResetCodePassword::query()->create($data);
        Mail::to($request['email'])->send(new SendCodeResetPassword($codeData['code']));
        return response()->json(['message'=>trans('code.sent')]);

    }

    public function userCheckcode(Request $request){
        $request->validate([
            'code'=>'required|string|exists:reset_code_passwords'
        ]);
        $passwordReset=ResetCodePassword::query()->firstWhere('code',$request['code']);
        if($passwordReset['created_at'] < now()->subHour()){
            $passwordReset->delete();
            return response()->json(['message'=>trans('code is expired')],422);
        }
        return response()->json([
            'code'=>$passwordReset['code'],
            'message'=>trans('code is valid')
            ]);
    }

    public function userResetPassword(Request $request){
        $input=$request->validate([
            'code'=>'required|string|exists:reset_code_passwords',
            'password'=>'required'
        ]);
        $passwordReset=ResetCodePassword::query()->firstWhere('code',$request['code']);
        if($passwordReset['created_at'] < now()->subHour()){
            $passwordReset->delete();
            return response()->json(['message'=>trans('password.code is expired')],422);
        }
        $user=User::query()->firstWhere('email',$passwordReset['email']);
        $input['password'] = Hash::make($input['password']);
        $user->update([
            'password'=>$input['password'],
        ]);
        $passwordReset->delete();
        return response()->json(['message'=>'password has been successfully reset']);
    }

    public function verifyEmailCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required'
    ]);

    $record = EmailVerificationCode::where('email', $request->email)
        ->where('code', $request->code)
        ->first();

    if (!$record) {
        return response()->json(['message' => 'Your code is incorrect'], 422);
    }

    if ($record->created_at < now()->subHour()) {
        $record->delete();
        return response()->json(['message' => 'code is expired'], 422);
    }

    $user = User::where('email', $request->email)->first();
    $user->email_verified_at = now();
    $user->save();

    $record->delete();

    return response()->json(['message' => 'Your email is confirmed']);
}

public function resendVerificationCode(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);
    $user = User::where('email', $request->email)->first();

    EmailVerificationCode::where('email', $user->email)->delete();
    $code = rand(100000, 999999);

    EmailVerificationCode::create([
        'email' => $user->email,
        'code' => $code,
        'created_at' => now(),
    ]);

    Mail::raw("Your verification code is $code", function ($message) use ($user) {
        $message->to($user->email)->subject('Resend verification code');
    });

    return response()->json(['message' => 'Resending verification code is done']);
}


    }

