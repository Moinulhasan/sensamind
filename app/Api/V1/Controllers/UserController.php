<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\AdminRequest;
use App\Api\V1\Requests\BluetoothClicksRequest;
use App\Api\V1\Requests\ClicksRequest;
use App\Api\V1\Requests\SignUpRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Api\V1\Requests\UserClicksRequest;
use App\BluetoothClicks;
use App\Buttons;
use App\User;
use App\UserClicks;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function userDetail(AdminRequest $request)
    {
        $user = Auth::guard()->user();
        if (($user->role == 'admin' || $user->role == 'super_admin') && $request->id) {
            $userData = User::find($request->id);
            if ($userData) {
                return response()->json([
                    'success' => true,
                    'user' => $userData,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => array([
                        'message' => 'User not found in system. Try again'
                    ])
                ], 404);
            }

        }
        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    /**
     * Get Users list
     *
     * @return JsonResponse
     */
    public function allUsers(AdminRequest $request)
    {
        $page = $request->page > 0 ? $request->page : 1;
        $limit = $request->limit > 0 ? $request->limit : 10;
        $offset = ($page - 1) * $limit;

        $userQuery = User::query();

        if ($request->role) {
            $currentUser = Auth::guard()->user();
            if ($currentUser->role == 'super_admin') {
                return response()->json([
                    'success' => true,
                    'users' => User::where('role', '=', 'super_admin')->get(),
                    'page' => $page,
                    'limit' => $limit,
                    'total' => User::where('role', '=', 'super_admin')->count()
                ], 200);
            }
        }

        if ($request->user_group && $request->user_group != '') {
            $userQuery->where('user_group', $request->user_group);
        }

        if ($request->search_key) {
            $users = $userQuery->where('email', 'LIKE', "{$request->search_key}%")->limit($limit)->offset($offset);
            return response()->json([
                'success' => true,
                'users' => $users->get(),
                'page' => $page,
                'limit' => $limit,
                'total' => $users->count()
            ], 200);
        }

        return response()->json([
            'success' => true,
            'users' => $userQuery->limit($limit)->offset($offset)->get(),
            'page' => $page,
            'limit' => $limit,
            'total' => $userQuery->count()
        ], 200);
    }

    public function updateUserDetails(AdminRequest $request, JWTAuth $JWTAuth)
    {
        if (count($request->all()) < 0) {
            return response()->json([
                'success' => false,
                'error' => array('message' => 'Couldn\'t update user.Try again')
            ], 422);
        }
        $params = $request->only('name', 'zipcode', 'age', 'gender', 'argued');
        $user = Auth::guard()->user();

        if ($user->role == 'super_admin' && !is_null($request->id)) {
            $tmpUser = User::find($request->id);
            if ($tmpUser) {
                $user = $tmpUser;
                if ($request->user_group) {
                    $user->fill(['user_group' => $request->user_group]);
                }
            }
        }

        $user->fill($params);

        if ($user->save()) {
            $newUser = User::where('id', '=', $user->id)->with(['userGroup', 'buttonOne', 'buttonTwo'])->first();
            return response()->json([
                'success' => true,
                'message' => 'User details updated successfully',
                'user' => $newUser
            ], 200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message' => 'Couldn\'t update user.Try again')
        ], 422);
    }

    /**
     * Set User Clicks
     *
     * @param ClicksRequest $request
     * @param JWTAuth $JWTAuth
     * @return JsonResponse
     */
    public function setClicks(ClicksRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        $clicks = [];

        foreach ($request->clicks as $click) {
            $click['evolution'] = $user->current_evolution;
            $click['user_group'] = $user->user_group;
            $clicks[] = new UserClicks($click);
        }

        if ($user->clicks()->saveMany($clicks)) {
            $currentEvolution = $user->current_evolution;
            if ($currentEvolution > 0 && $currentEvolution < 4) {
                if ($this->shouldSwitchEvolution($user->id)) {
                    $maxClickedButton = $this->getMaxClickedButton($user);
                    if ($maxClickedButton && $maxClickedButton->branch1 && $maxClickedButton->branch2) {
                        $btn1 = Buttons::where('user_group', '=', $user->user_group)->where('node', '=', $maxClickedButton->branch1)->first();
                        $btn2 = Buttons::where('user_group', '=', $user->user_group)->where('node', '=', $maxClickedButton->branch2)->first();
                        $evolutionPath = $user->evolution_path ? $user->evolution_path . "-" . "E" . $currentEvolution . ":B" . $maxClickedButton->id : "E1:B" . $maxClickedButton->id;
                        $newCurrentEvolution = $currentEvolution + 1;

                        $updateFields = [
                            'current_evolution' => $newCurrentEvolution,
                            'evolution_path' => $evolutionPath,
                            'current_btn1' => $btn1['id'],
                            'current_btn2' => $btn2['id']
                        ];

                        $user->fill($updateFields);
                        if ($user->save()) {
                            $newUser = User::where('id', '=', $user->id)->with(['userGroup', 'buttonOne', 'buttonTwo'])->first();
                            return response()->json([
                                'success' => true,
                                'message' => 'Click(s) saved successfully',
                                'user' => $newUser
                            ], 200);
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Click(s) saved successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Couldn\'t save clicks'
        ], 422);
    }

    /**
     * Set User Bluetooth Clicks
     *
     * @param ClicksRequest $request
     * @param JWTAuth $JWTAuth
     * @return JsonResponse
     */
    public function setBluetoothClicks(BluetoothClicksRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        $clicks = [];

        foreach ($request->clicks as $click) {
            $click['evolution'] = $user->current_evolution;
            $click['user_group'] = $user->user_group;
            $clicks[] = new BluetoothClicks($click);
        }

        if ($user->bluetoothClicks()->saveMany($clicks)) {
            return response()->json([
                'success' => true,
                'message' => 'Click(s) saved successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Couldn\'t save click'
        ], 422);
    }

    /**
     * List User clicks
     *
     * @param UserClicksRequest $request
     * @param JWTAuth $JWTAuth
     * @return JsonResponse
     */
    public function getClicks(UserClicksRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        if ($user->role !== 'user') {
            $clicks = $this->getFilteredClicks($request);
            return response()->json([
                'success' => true,
                'clicks' => $clicks,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'clicks' => $this->getAllClicks($request->id),
        ], 200);
    }


    public function getMyStatistics(AdminRequest $request)
    {
        $user = Auth::guard()->user();
        $userId = $user->id;

        if ($user->role != 'user' && is_null($request->id)) {
            return $this->getStatisticsByFilter($request);
        }

        if (($user->role !== 'user') && !is_null($request->id)) {
            $user = User::findOrFail($request->id);
            $userId = $request->id;
        }
        $today = Carbon::today('UTC');
        $yesterday = Carbon::yesterday('UTC');
        $firstClick = $user->clicks()->orderBy('clicked_at', 'ASC')->first();

        $todayClicks = $user->clicks()->whereDate('clicked_at', $today);
        $yesterdayClicks = $user->clicks()->whereDate('clicked_at', $yesterday);
        $overallClicks = $user->clicks()->where('evolution', $user->current_evolution)->get();

        $todayButtonClicks = $this->getClicksGroupedBy($today, 'button', $userId);
        $yesterdayButtonClicks = $this->getClicksGroupedBy($yesterday, 'button', $userId);
        $overallButtonClicks = $this->getClicksGroupedBy(null, 'button', $userId);

        $todayCauseClicks = $this->getClicksGroupedBy($today, 'cause', $userId);
        $yesterdayCauseClicks = $this->getClicksGroupedBy($yesterday, 'cause', $userId);
        $overallCauseClicks = $this->getClicksGroupedBy(null, 'cause', $userId);

        $todayLabels = array('button1' => $user->buttonOne->button_label, 'button2' => $user->buttonTwo->button_label);
        $yesterdayLabels = array('button1' => $user->buttonOne->button_label, 'button2' => $user->buttonTwo->button_label);

        return response()->json([
            'success' => true,
            'today' => array('button_clicks' => $todayButtonClicks, 'cause_clicks' => $todayCauseClicks, 'button_1_label' => $todayLabels['button1'], 'button_2_label' => $todayLabels['button2'], 'total' => $todayClicks->count()),
            'yesterday' => array('button_clicks' => $yesterdayButtonClicks, 'cause_clicks' => $yesterdayCauseClicks, 'button_1_label' => $yesterdayLabels['button1'], 'button_2_label' => $yesterdayLabels['button2'], 'total' => $yesterdayClicks->count()),
            'overall' => array('button_clicks' => $overallButtonClicks, 'cause_clicks' => $overallCauseClicks, 'button_1_label' => $todayLabels['button1'], 'button_2_label' => $todayLabels['button2'], 'total' => $overallClicks->count(), 'first_click' => $firstClick ? $firstClick['clicked_at'] : Carbon::now('UTC')),
            'bluetooth_clicks' => $user->bluetoothClicks()->count(),
        ], 200);
    }

    /**
     * Get Bluetooth Clicks count
     */

    function getBluetoothClickStats(AdminRequest $request)
    {
        $user = Auth::guard()->user();
        $userId = $request->id;

        if (($user->role == 'admin' || $user->role == 'super_admin') && !is_null($userId)) {
            $user = User::findOrFail($userId);
        }

        if ($user) {
            return response()->json([
                'success' => true,
                'total_bluetooth_clicks' => $user->bluetoothClicks()->count()
            ], 200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message' => 'Something went wrong. Couldn\'t find user details.')
        ], 422);

    }

    /**
     *  Get Clicks made between a time interval
     *
     * @param $startDate
     * @param $endDate
     * @param $request
     *
     * @return UserClicks|Builder[]|Collection
     */

    private function getUserClicksBetweenDate($request)
    {
        $user = Auth::guard()->user();
        $clicksQuery = UserClicks::query();
        if (($user->role == 'admin' || $user->role == 'super_admin')) {
            if (!is_null($request->id)) {
                $user = User::findOrFail($request->id);
                $clicksQuery->where('user_id', $user->id);
            }
        } else {
            $clicksQuery->where('user_id', $user->id);
        }

        if ($request->user_group && $request->user_group != '') {
            $clicksQuery->where('user_group', $request->user_group);
        }
        if ($request->evolution && $request->evolution != '') {
            $clicksQuery->where('evolution', $request->evolution);
        }
        if ($request->start_date && $request->start_date != '' && $request->end_date && $request->end_date != '') {
            $clicksQuery->whereBetween('clicked_at', [$request->start_date, $request->end_date]);
        }

        return $clicksQuery->orderBy('clicked_at', 'ASC')->get();
    }

    /**
     * Get all clicks
     *
     * @return UserClicks
     */

    private function getAllClicks($id = null)
    {
        $user = Auth::guard()->user();
        if (($user->role == 'admin' || $user->role == 'super_admin') && !is_null($id)) {
            $user = User::find($id);
        }

        return $user->clicks()->orderBy('clicked_at', 'ASC')->get();
    }

    /**
     *
     * Get clicks grouped by key
     * @param $date
     * @param string $key
     * @param null $id
     * @return Mixed
     */

    private function getClicksGroupedBy($date, $key = 'button_id', $id = null)
    {
        $user = Auth::guard()->user();
        if (($user->role == 'admin' || $user->role == 'super_admin') && !is_null($id)) {
            $user = User::findOrFail($id);
        }
        if ($date) {
            return $user->clicks()->whereDate('clicked_at', $date)->groupBy($key)->orderBy('clicked_at', 'ASC')->get([$key, DB::raw('CAST(count(*) AS UNSIGNED) as total')]);
        }
        return $user->clicks()->groupBy($key)->orderBy('clicked_at', 'ASC')->get([$key, DB::raw('CAST(count(*) AS UNSIGNED) as total')]);
    }

    /**
     *
     * Check if user should be moved to next evolution and return new button
     *
     * @param userId
     * @return bool
     *
     */

    private function shouldSwitchEvolution($userId)
    {

        #get clicks count in past 3 days || in dev to 1 Hour
        $pivotDate = Carbon::now('UTC')->subDays(2);
        $clicksMadeCount = UserClicks::where('user_id', $userId)->where('clicked_at', '>', $pivotDate)->count();

        if ($clicksMadeCount < 3) {
            return true;
        }
        return false;
    }

    /**
     * Get max clicked button in the current Evolution for the user
     *
     * @param $user
     * @return Buttons|Builder[]|Collection
     */

    private function getMaxClickedButton($user)
    {

        $clicksQuery = UserClicks::query();
        $clicksQuery->where('user_id', $user->id);
        $clicksQuery->where('evolution', $user->current_evolution);
        $clicksQuery->orderBy('clicked_at', 'DESC');
        $preconditionCheckQuery = clone $clicksQuery;
        $precondition = $preconditionCheckQuery->first();
        if ($precondition) {
            if ((Carbon::now('UTC')->diffInHours($precondition['clicked_at'])) < 3) {
                return null;
            } else {
                $clicksQuery->groupBy(['button_id']);
                $clicksQuery->orderBy('total', 'DESC');
                $maxButtonIdAndCount = $clicksQuery->first(['button_id', DB::raw('CAST(count(*) AS UNSIGNED) as total')]);
                $maxClickedButton = Buttons::where('id', $maxButtonIdAndCount['button_id'])->first();
                return $maxClickedButton;
            }
        } else {
            return null;
        }

    }

    private function getFilteredClicks($request)
    {
        $clicksQuery = UserClicks::query();
        $userQuery = User::query();
        $byUser = false;

        if ($request->id) {
            $userQuery->where('id', '=', $request->id);
        }
        else {
            if ($request->gender && $request->gender < 3) {
                $byUser = true;
                $userQuery->where('gender', '=', $request->gender);
            }
            if ($request->age) {
                $ageRange = preg_split("/[-\s:]/", $request->age);
                $minAge = $ageRange[0];
                $maxAge = $ageRange[1];
                if ($minAge < 100 && $maxAge < 100) {
                    $byUser = true;
                    $userQuery->whereBetween('age', [$minAge, $maxAge]);
                }
            }
            if ($request->zipcode && strlen($request->zipcode) > 0) {
                $byUser = true;
                $userQuery->whereRaw("UPPER(zipcode) LIKE '" . strtoupper($request->zipcode) . "%'");
            }
        }

        $users = $userQuery->get(['id']);

        if ($request->user_group) {
            $clicksQuery->where('user_group', '=', $request->user_group);
        }
        if ($request->evolution) {
            $clicksQuery->where('evolution', '=', $request->evolution);
        }
        if ($request->time_range) {
            $clicksQuery->where('clicked_at', '>', Carbon::now('UTC')->subHours($request->time_range));
        }
        if ($byUser) {
            $clicksQuery->whereIn('user_id', $users);
        }
        return $clicksQuery->get();
    }

    private function getStatisticsByFilter($request)
    {
        $clicksQuery = UserClicks::query();
        $userQuery = User::query();
        $byUser = false;

        if ($request->gender && $request->gender < 3) {
            $byUser = true;
            $userQuery->where('gender', '=', $request->gender);
        }
        if ($request->age) {
            $ageRange = preg_split("/[-\s:]/", $request->age);
            $minAge = $ageRange[0];
            $maxAge = $ageRange[1];
            if ($minAge < 100 && $maxAge < 100) {
                $byUser = true;
                $userQuery->whereBetween('age', [$minAge, $maxAge]);
            }
        }
        if ($request->zipcode && strlen($request->zipcode) > 0) {
            $byUser = true;
            $userQuery->whereRaw("UPPER(zipcode) LIKE '" . strtoupper($request->zipcode) . "%'");
        }

        $users = $userQuery->get(['id']);

        if ($request->user_group) {
            $clicksQuery->where('user_group', '=', $request->user_group);
        }
        if ($request->evolution) {
            $clicksQuery->where('evolution', '=', $request->evolution);
        }
        if ($request->time_range) {
            $clicksQuery->whereDate('clicked_at', '>', Carbon::now('UTC')->subHours($request->time_range));
        }
        if ($byUser) {
            $clicksQuery->whereIn('user_id', $users);
        }

        $overClicksQuery = clone $clicksQuery;
        $causeQuery = clone $clicksQuery;
        $firstClickQuery = clone $clicksQuery;
        $overallClicks = $overClicksQuery->count();
        $firstClick = $firstClickQuery->orderBy('clicked_at', 'ASC')->first();
        $overallButtonClicks = $clicksQuery->groupBy(['button_id'])->orderBy('clicked_at', 'ASC')->get(['button', DB::raw('CAST(count(*) AS UNSIGNED) as total')]);
        $overallCauseClicks = $causeQuery->groupBy(['cause'])->orderBy('clicked_at', 'ASC')->get(['cause', DB::raw('CAST(count(*) AS UNSIGNED) as total')]);

        return response()->json([
            'success' => true,
            'users' => $users,
            'overall' => array('button_clicks' => $overallButtonClicks, 'cause_clicks' => $overallCauseClicks, 'total' => $overallClicks, 'first_click' => $firstClick ? $firstClick['clicked_at'] : Carbon::now('UTC')),
        ], 200);
    }
}
