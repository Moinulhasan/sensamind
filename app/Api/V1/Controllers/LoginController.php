<?php

namespace App\Api\V1\Controllers;

use App\Mail\UnlockAccount;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;

class LoginController extends Controller
{
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);
        $maxLoginAttempts = config('auth.max_login_failures');

        $email = $request->email;
        $attemptedUser = $this->getUserByEmail($email);
        if (is_null($attemptedUser)) {
            return response()
                ->json([
                    'success' => false,
                    'error' => array('message' => 'Invalid credentials. Try again')
                ], 401);
        }
        $loginAttempts = $attemptedUser->failed_logins;

        if ($loginAttempts > $maxLoginAttempts) {
            return response()
                ->json([
                    'success' => false,
                    'error' => array('message' => 'Account locked due to too many failed attempts. Check your email for account unlocking steps.')
                ], 401);
        }
        if ($loginAttempts == $maxLoginAttempts) {
            $attemptedUser->failed_logins = $maxLoginAttempts + 1;
            $lockCode = str_random(40);
            $attemptedUser->lock_out_code = $lockCode;
            $attemptedUser->save();

            try {
                $this->sendAccountUnlockEmail($attemptedUser,$lockCode);
            }
            catch (\Exception $e){
                return response()
                    ->json([
                        'success' => false,
                        'error' => array('message' => 'Account locked. Error sending unlock instruction. Contact us')
                    ], 500);
            }

            return response()
                ->json([
                    'success' => false,
                    'error' => array('message' => 'Account locked. Email with instruction to unlock account sent')
                ], 401);
        }

        try {
            $token = Auth::guard()->attempt($credentials);

            if (!$token) {
                $attemptedUser->failed_logins = $attemptedUser->failed_logins + 1;
                $attemptedUser->save();
                return response()
                    ->json([
                        'success' => false,
                        'error' => array('message' => 'Invalid credentials. Try again')
                    ], 401);
            }

        } catch (JWTException $e) {
            $attemptedUser->failed_logins = $attemptedUser->failed_logins + 1;
            $attemptedUser->save();
            return response()
                ->json([
                    'success' => false,
                    'error' => array('message' => 'Something went wrong. Try again')
                ], 500);
        }

        $user = Auth::guard()->user();

        if ($user->is_verified == 0) {
            return response()
                ->json([
                    'success' => false,
                    'error' => array('message' => 'Please verify your account and Try again')
                ], 401);
        }
        if ($user->failed_logins > 0) {
            $user->failed_logins = 0;
            $user->save();
        }

        return response()
            ->json([
                'success' => true,
                'token' => $token,
                'user' => $user,
                'expires_in' => Auth::guard()->factory()->getTTL() * 60
            ]);
    }

    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }


    public function sendAccountUnlockEmail($user,$token)
    {
        $baseUrl = env('BASE_APP_URL', 'http://localhost:8000');
        $email = $user->email;
        $name = $user->name;
        $actionUrl = $baseUrl . '/unlock/' . $token;
        $details = ['name' => $name, 'actionUrl' => $actionUrl];
        Mail::to($email)->send(new UnlockAccount($details));
    }
}
