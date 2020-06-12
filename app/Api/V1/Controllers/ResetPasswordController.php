<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use App\Api\V1\Requests\ResetPasswordRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResetPasswordController extends Controller
{
    public function resetPassword(ResetPasswordRequest $request, JWTAuth $JWTAuth)
    {
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->reset($user, $password);
            }
        );
        $errorMessage = null;
        switch ($response){
            case Password::PASSWORD_RESET:
                $errorMessage = null;
                break;
            case Password::INVALID_USER:
                $errorMessage = 'Invalid user detail. User not found';
                break;
            case Password::INVALID_TOKEN:
                $errorMessage = "Password reset link expired";
                break;
            case Password::INVALID_PASSWORD:
                $errorMessage = "Invalid password. Try different password";
                break;
        }

        if($errorMessage) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => $response
            ],422);
        }

        if(!Config::get('boilerplate.reset_password.release_token')) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        }

        $user = User::where('email', '=', $request->get('email'))->first();

        return response()->json([
            'success' => true,
            'token' => $JWTAuth->fromUser($user)
        ]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  ResetPasswordRequest  $request
     * @return array
     */
    protected function credentials(ResetPasswordRequest $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function reset($user, $password)
    {
        $user->password = $password;
        $user->save();
    }
}
