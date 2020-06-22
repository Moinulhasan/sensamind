<?php

namespace App\Api\V1\Controllers;

use App\Mail\VerifyEmail;
use App\UserVerification;
use Config;
use App\User;
use Illuminate\Support\Facades\Mail;
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
        $params = $request->only('name', 'email', 'password','zipcode');
        $user = new User($params);
        if(!$user->save()) {
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Couldn\'t create user. Try again')
            ], 422);
        }

        if(!Config::get('boilerplate.sign_up.release_token')) {
            $verification_code = str_random(40);
            $verificationParam = array('user_id'=>$user->id,'token'=>$verification_code);
            $verification = new UserVerification($verificationParam);

            if($verification->save()){
                try{
                    $this->sendVerificationEmail($user,$verification_code);
                }
                catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'summary' => $e->getMessage(),
                        'error' => array('message'=>'Couldn\'t send verification mail. Try again')
                    ], 422);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully. Kindly verify your email to start using the app'
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

    public function sendVerificationEmail($user,$token)
    {
        $baseUrl = env('BASE_APP_URL', 'http://localhost:8000');
        $email = $user->email;
        $name = $user->name;
        $actionUrl = $baseUrl . '/auth/verify-account/' . $token;
        $details = ['name' => $name, 'actionUrl' => $actionUrl];
        Mail::to($email)->send(new VerifyEmail($details));
    }
}
