<?php

namespace App\Api\V1\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Auth;

class LoginController extends Controller
{
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);

        try {
            $token = Auth::guard()->attempt($credentials);

            if(!$token) {
                return response()
                    ->json([
                        'success' => false,
                        'error' => array('message'=>'Invalid credentials. Try again')
                    ],401);
            }

        } catch (JWTException $e) {
            return response()
                ->json([
                    'success' => false,
                    'error' => array('message'=>'Something went wrong. Try again')
                ],500);
        }
        $user = Auth::guard()->user();

        if($user->is_verified == 0){
            return response()
                ->json([
                    'success' => false,
                    'error' => array('message'=>'Please verify your account and Try again')
                ],401);
        }

        return response()
            ->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'expires_in' => Auth::guard()->factory()->getTTL() * 60
            ]);
    }
}
