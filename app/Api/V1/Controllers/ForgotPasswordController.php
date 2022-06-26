<?php

namespace App\Api\V1\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use App\Api\V1\Requests\ForgotPasswordRequest;

class ForgotPasswordController extends Controller
{
    public function sendResetEmail(ForgotPasswordRequest $request)
    {
        $user = User::where('email', '=', $request->get('email'))->first();

        if(!$user) {
            return response()->json([
                'success' => false,
                'error' => array('message' =>'Email address not found in our system. Check email id and try again')
            ], 404);
        }

        $broker = $this->getPasswordBroker();
        $sendingResponse = $broker->sendResetLink($request->only('email'));

        if($sendingResponse !== Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'message' => array('message' =>'Couldn\'t send password reset link. Kindly try after sometime')
            ], 501);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset instruction email sent successfully'
        ], 200);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    private function getPasswordBroker()
    {
        return Password::broker();
    }
}
