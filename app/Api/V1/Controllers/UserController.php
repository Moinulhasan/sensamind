<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ClicksRequest;
use App\Api\V1\Requests\UserClicksRequest;
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

        $clicks = [];
        foreach ($request->clicks as $click) {
            $clicks[] = new UserClicks($click);
        }

        if($user->clicks()->saveMany($clicks)){
            return response()->json([
                'success'=> true,
                'message' => 'Click(s) saved successfully'
            ],200);
        }

        return response()->json([
            'success'=> false,
            'message' => 'Couldn\'t save click'
        ],422);
    }

    /**
     * List current User clicks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClicks(UserClicksRequest $request,JWTAuth $JWTAuth)
    {
        if($request->start_date && $request->end_date){
            $clicks = $this->getUserClicksBetweenDate($request->start_date,$request->end_date);

            return response()->json([
                'success' => true,
                'clicks' => $clicks,
            ],200);
        }

        return response()->json([
                'success' => true,
                'clicks' => $this->getAllClicks(),
            ],200);
    }

    private function getUserClicksBetweenDate($startDate,$endDate)
    {
        $user = Auth::guard()->user();
        return $user->clicks()->whereBetween('clicked_at', [$startDate,$endDate])->get();
    }

    private function getAllClicks()
    {
        $user = Auth::guard()->user();
        return $user->clicks()->get();
    }

}
