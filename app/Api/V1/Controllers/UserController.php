<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\AdminRequest;
use App\Api\V1\Requests\BluetoothClicksRequest;
use App\Api\V1\Requests\ClicksRequest;
use App\Api\V1\Requests\SignUpRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Api\V1\Requests\UserClicksRequest;
use App\BluetoothClicks;
use App\Evolutions;
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
    public function userDetail(AdminRequest $request)
    {
        $user = Auth::guard()->user();
        if($user->role == 'admin' && $request->id){
            $userData = User::find($request->id);
            if($userData && $userData->id){
                $evolution = Evolutions::with(['buttonOne','buttonTwo'])->find($userData->current_evolution);
                return response()->json([
                    'success' => true,
                    'user' => $userData,
                    'evolution' => $evolution
                ],200);
            }
            else {
                return response()->json([
                    'success' => false,
                    'error' => array([
                        'message' => 'User not found in system. Try again'
                    ])
                ],404);
            }

        }
        $evolution = Evolutions::with(['buttonOne','buttonTwo'])->find($user->current_evolution);
        return response()->json([
            'success' => true,
            'user' => $user,
            'evolution' => $evolution
        ]);
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

    public function updateUserDetails(AdminRequest $request,JWTAuth $JWTAuth)
    {
        if(count($request->all()) < 0){
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Couldn\'t update user.Try again')
            ],422);
        }
        $params = $request->only('name','zipcode','age','gender','argued');
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
            ],200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t update user.Try again')
        ],422);
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
        $currentEvolution = $user->current_evolution;
        $clicks = [];

        foreach ($request->clicks as $click) {
            $click['evolution'] = $currentEvolution;
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
     * Set User Bluetooth Clicks
     *
     * @param ClicksRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function setBluetoothClicks(BluetoothClicksRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        $currentEvolution = $user->current_evolution;
        $clicks = [];

        foreach ($request->clicks as $click) {
            $click['evolution'] = $currentEvolution;
            $clicks[] = new BluetoothClicks($click);
        }

        if($user->bluetoothClicks()->saveMany($clicks)){
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

        $todayCauseClicks = $this->getClicksOfDayGroupedBy($today,'cause',$userId);
        $yesterdayCauseClicks = $this->getClicksOfDayGroupedBy($yesterday,'cause',$userId);

        $todayFirstClick = $todayClicks->first();
        $yesterdayFirstClick = $yesterdayClicks->first();

        $todayEvolution = Evolutions::first();
        $yesterdayEvolution = Evolutions::first();

        $todayLabels = array('button1' => "Button 1", 'button2' => 'Button 2');
        $yesterdayLabels = array('button1' => "Button 11", 'button2' => 'Button 22');

        if($todayFirstClick){
            $todayLabels = array('button1' =>Labels::find($todayEvolution->button_1),'button2' => Labels::find($todayEvolution->button_2));
        }
        if($yesterdayFirstClick){
            $yesterdayLabels = array('button1' =>Labels::find($yesterdayEvolution->button_1),'button2' => Labels::find($yesterdayEvolution->button_2));
        }

        return response()->json([
            'success' => true,
            'today' => array('button_clicks' => $todayButtonClicks,'cause_clicks'=>$todayCauseClicks,'button_1_label'=>$todayLabels['button1'], 'button_2_label'=> $todayLabels['button2']),
            'yesterday' => array('button_clicks' => $yesterdayButtonClicks,'cause_clicks'=>$yesterdayCauseClicks,'button_1_label'=>$yesterdayLabels['button1'], 'button_2_label'=> $yesterdayLabels['button2']),
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
        return $user->clicks()->whereDate('clicked_at', $date)->groupBy($key)->orderBy('total','desc')->get([$key, \DB::raw('CAST(count(*) AS UNSIGNED) as total')]);
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
