<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CreateUserRequest;
use App\Models\Buttons;
use App\Mail\NewAccountByAdmin;
use App\Mail\VerifyEmail;
use App\Models\UserGroups;
use App\Models\UserVerification;
use Config;
use Illuminate\Support\Str;
use Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            $verification_code =Str::random(40);
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

    public function createUser(CreateUserRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        if($user->role == 'super_admin' || ($user->role == 'admin' && $user->user_group == $request->user_group)){
            if($this->getUserByEmail($request->email)){
                return response()->json([
                    'success' => false,
                    'error' => array('message'=>'Email address already in use.')
                ],422);
            }

            $params = $request->only('name', 'email','zipcode','age','gender','role','user_group');
            $newUser = new User($params);
            $userGroupDetails = Buttons::where('user_group',$request->user_group)->limit(2)->get();
            $button1 = $userGroupDetails[0]['id'];
            $button2 = $userGroupDetails[1]['id'];
            $newUser->current_btn1 = $button1;
            $newUser->current_btn2 = $button2;
            $password =Str::random(12);
            $newUser->password = $password;

            if(!$newUser->save()) {
                return response()->json([
                    'success' => false,
                    'error' => array('message'=>'Couldn\'t create user. Try again')
                ], 422);
            }

            $verification_code =Str::random(40);
            $verificationParam = array('user_id'=>$newUser->id,'token'=>$verification_code);
            $verification = new UserVerification($verificationParam);

            if($verification->save()){
                try{
                    $this->sendAccountCreationMail($newUser,$verification_code,$password);
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
                'message' => 'Account created successfully. Sent Email to confirm account'
            ], 201);
        }

        return response()->json([
            'success' => false,
            'error' => array('message' => 'Un authorised request')
        ],403);
    }



    private function getUserByEmail($email){
        return User::where('email',$email)->first();
    }

    public function sendVerificationEmail($user,$token)
    {
        $baseUrl = Config::get('app.base_url');
        $email = $user->email;
        $name = $user->name;
        $actionUrl = $baseUrl . '/auth/verify-account/' . $token;
        $details = ['name' => $name, 'actionUrl' => $actionUrl];
        Mail::to($email)->send(new VerifyEmail($details));
    }

    public function sendAccountCreationMail($user,$token,$password)
    {
        $baseUrl = Config::get('app.base_url');
        $email = $user->email;
        $name = $user->name;
        $actionUrl = $baseUrl . '/auth/verify-account/' . $token;
        $details = ['name' => $name, 'actionUrl' => $actionUrl,'password' => $password,'email' => $email];
        Mail::to($email)->send(new NewAccountByAdmin($details));
    }
}
