<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ClicksRequest;
use App\Api\V1\Requests\UserClicksRequest;
use App\Labels;
use App\User;
use App\UserClicks;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
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
     * @param ClicksRequest $request
     * @param JWTAuth $JWTAuth
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
     * @param UserClicksRequest $request
     * @param JWTAuth $JWTAuth
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


    public function getMyStatistics()
    {
        $user = Auth::guard()->user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayClicks = $user->clicks()->whereDate('clicked_at', $today);
        $yesterdayClicks = $user->clicks()->whereDate('clicked_at', $yesterday);

        $todayButtonClicks = $this->getClicksOfDayGroupedBy($today,'button');
        $yesterdayButtonClicks = $this->getClicksOfDayGroupedBy($yesterday,'button');

        $todayCauseClicks = $this->getClicksOfDayGroupedBy($today,'cause');
        $yesterdayCauseClicks = $this->getClicksOfDayGroupedBy($yesterday,'cause');

        $todayFirstClick = $todayClicks->first();
        $yesterdayFirstClick = $yesterdayClicks->first();

        $todayLabel = Labels::first();
        $yesterdayLabel = Labels::first();
        if($todayFirstClick){
            $todayLabel = Labels::find($todayFirstClick->current_set);
        }
        if($yesterdayFirstClick){
            $yesterdayLabel = Labels::find($yesterdayFirstClick->current_set);
        }

        return response()->json([
            'success' => true,
            'today' => array('button_clicks' => $todayButtonClicks,'cause_clicks'=>$todayCauseClicks,'label'=>$todayLabel),
            'yesterday' => array('button_clicks' => $yesterdayButtonClicks,'cause_clicks'=>$yesterdayCauseClicks,'label'=>$yesterdayLabel),
        ], 200);
    }


    /**
     *  Get Clicks made between a time interval
     *
     * @param $startDate
     * @param $endDate
     *
     * @return UserClicks
     */

    private function getUserClicksBetweenDate($startDate,$endDate)
    {
        $user = Auth::guard()->user();
        return $user->clicks()->whereBetween('clicked_at', [$startDate,$endDate])->get();
    }

    /**
     * Get all clicks
     *
     * @return UserClicks
     */

    private function getAllClicks()
    {
        $user = Auth::guard()->user();
        return $user->clicks()->get();
    }

    /**
     *
     * Get clicks grouped by key
     * @return Mixed
     *
     */

    private function getClicksOfDayGroupedBy($date,$key)
    {
        $user = Auth::guard()->user();
        return $user->clicks()->whereDate('clicked_at', $date)->groupBy($key)->orderBy('total','desc')->get([$key, \DB::raw('count(*) as total')]);
    }

    /**
     * Get Max value in array of objects
     *
     * @return Integer
     */

    private function findMaxValueInArray($array,$key)
    {
        return max(array_column($array, $key));
    }
}
