<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\AdminRequest;
use App\Api\V1\Requests\ClicksRequest;
use App\Api\V1\Requests\SignUpRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Api\V1\Requests\UserClicksRequest;
use App\Labels;
use App\User;
use App\UserClicks;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Integer;
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

    public function updateUserDetails(SpecificResourceRequest $request,JWTAuth $JWTAuth)
    {
        $params = $request->only('name','zipcode','age');
        $user = Auth::guard()->user();

        if($user->role == 'admin' && !is_null($request->id)){
            $user = User::findOrFail($request->id);
        }
        $user->fill($params);

        if($user->save()){
            return response()->json([
                'success' => true,
                'message' => 'User details updated successfully',
                'user'=>$user
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t update user.Try again')
        ]);
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
     * List User clicks
     *
     * @param UserClicksRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClicks(UserClicksRequest $request,JWTAuth $JWTAuth)
    {
        if($request->start_date && $request->end_date){
            $clicks = $this->getUserClicksBetweenDate($request->start_date,$request->end_date,$request->id);

            return response()->json([
                'success' => true,
                'clicks' => $clicks,
            ],200);
        }

        return response()->json([
                'success' => true,
                'clicks' => $this->getAllClicks($request->id),
            ],200);
    }


    public function getMyStatistics(AdminRequest $request)
    {
        $user = Auth::guard()->user();
        $userId = $request->id;

        if($user->role == 'admin' && !is_null($userId)){
            $user = User::findOrFail($userId);
        }
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayClicks = $user->clicks()->whereDate('clicked_at', $today);
        $yesterdayClicks = $user->clicks()->whereDate('clicked_at', $yesterday);

        $todayButtonClicks = $this->getClicksOfDayGroupedBy($today,'button',$userId);
        $yesterdayButtonClicks = $this->getClicksOfDayGroupedBy($yesterday,'button',$userId);

        $todayCauseClicks = $this->getClicksOfDayGroupedBy($today,'cause');
        $yesterdayCauseClicks = $this->getClicksOfDayGroupedBy($yesterday,'cause',$userId);

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

    private function getUserClicksBetweenDate($startDate,$endDate,$id=null)
    {
        $user = Auth::guard()->user();
        if($user->role == 'admin' && !is_null($id)){
            $user = User::findOrFail($id);
        }
        return $user->clicks()->whereBetween('clicked_at', [$startDate,$endDate])->get();
    }

    /**
     * Get all clicks
     *
     * @return UserClicks
     */

    private function getAllClicks($id=null)
    {
        $user = Auth::guard()->user();
        if($user->role == 'admin' && !is_null($id)){
            $user = User::findOrFail($id);
        }
        return $user->clicks()->get();
    }

    /**
     *
     * Get clicks grouped by key
     * @return Mixed
     *
     */

    private function getClicksOfDayGroupedBy($date,$key,$id=null)
    {
        $user = Auth::guard()->user();
        if($user->role == 'admin' && !is_null($id)){
            $user = User::findOrFail($id);
        }
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
