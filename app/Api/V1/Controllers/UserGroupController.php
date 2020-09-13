<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\AdminRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Api\V1\Requests\UserGroupRequest;
use App\Http\Controllers\Controller;
use App\UserGroups;

class UserGroupController extends Controller
{
    /**
     * List all UserGroups
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserGroups()
    {
        $groups = UserGroups::all();

        return response()->json([
            'success' => true,
            'user_groups' => $groups
        ]);
    }

    /**
     * UserGroup
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserGroup(UserGroupRequest $request)
    {
        $params = $request->all();
        $userGroup = new UserGroups($params);

        if($userGroup->save()){
            return response()->json([
                'success' => true,
                'message' => 'User groups added successfully'
            ]);
        }
        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t add user group.Try again')
        ]);
    }

    /**
     * UserGroup
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserGroup(SpecificResourceRequest $request)
    {
        $params = $request->all();
        $userGroup = UserGroups::find($request->id);

        if(!$userGroup){
            return response()->json([
                'success' => false,
                'error' => array('message'=>'User group not found. Try again')
            ],404);
        }

        $userGroup->fill($params);

        if($userGroup->save()){
            return response()->json([
                'success' => true,
                'message' => 'User Group updated successfully'
            ],200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t update user group.Try again')
        ],404);
    }

}