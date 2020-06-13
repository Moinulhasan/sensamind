<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\VerifyAccountRequest;
use App\Http\Controllers\Controller;
use App\UserVerification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use Tymon\JWTAuth\JWTAuth;

class VerifyAccountController extends Controller
{
    public function verifyAccount(VerifyAccountRequest $request, JWTAuth $JWTAuth)
    {
        $verification_code = $request->verification_code;
        $check = UserVerification::where('token', $verification_code)->first();

        if (!is_null($check)) {
            $user = User::find($check->user_id);

            if ($user->is_verified == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account already verified.'
                ]);
            }
            $user->is_verified = 1;
            $user->email_verified_at = Carbon::now()->toDateTimeString();

            if($user->save()){
                UserVerification::where('token', $verification_code)->delete();
            }
            else {
                return response()->json([
                        'success' => false,
                        'error' => array('message' => 'Verification code is invalid.')],500
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'You have successfully verified your email address.'
            ],200);
        }

        return response()->json([
                'success' => false,
                'error' => array('message' => 'Verification code is invalid.')],403
        );

    }
}