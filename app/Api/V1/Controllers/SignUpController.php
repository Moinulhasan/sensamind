<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SignUpController extends Controller
{
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        if($this->getUserByEmail($request->email)){
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Email address already in use.')
            ],422);
        }

        $user = new User($request->all());
        if(!$user->save()) {
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Couldn\'t create user. Try again')
            ], 422);
        }

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully.Verify email to start using the app'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'success' => true,
            'token' => $token
        ], 201);
    }

    private function getUserByEmail($email){
        return User::where('email',$email)->first();
    }
}
