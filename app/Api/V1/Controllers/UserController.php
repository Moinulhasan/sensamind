<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ClicksRequest;
use App\User;
use App\UserClicks;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use Auth;

class UserController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', []);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::guard()->user());
    }
    /**
     * Get Users list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allUsers()
    {
        return response()->json(['success' => true,'users' =>User::all()]);
    }

    /**
     * Set User Clicks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setClicks(ClicksRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        if(!$user){
            return response()->json([
                'success'=> false,
                'message' => 'Please check credentials and try again'
            ],401);
        }

        $clicks = [];
        foreach ($request->clicks as $click) {
            $clicks[] = new UserClicks($click);
        }

        try{
            if($user->clicks()->saveMany($clicks)){
                return response()->json([
                    'success'=> true,
                    'message' => 'Click(s) saved successfully'
                ],200);
            }
        }
        catch (\Exception $e){
            return response()->json([
                'success'=> false,
                'message' => 'Couldn\'t save click'
            ],422);
        }

        return response()->json([
            'success'=> false,
            'message' => 'Couldn\'t save click'
        ],422);
    }

}
