<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\AdminRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Api\V1\Requests\UserGroupRequest;
use App\Buttons;
use App\Http\Controllers\Controller;
use App\UserGroups;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserGroupController extends Controller
{
    /**
     * List all UserGroups
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserGroups()
    {
        $groups = UserGroups::withCount('users')->get();

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

        if ($userGroup->save()) {
            Log::error($userGroup);
            if($this->createDefaultButtons($userGroup->id)){
                return response()->json([
                    'success' => true,
                    'message' => 'User group and it\'s default evolution button labels added successfully'
                ]);
            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating evolution labels for user group'
                ]);
            }
        }
        return response()->json([
            'success' => false,
            'error' => array('message' => 'Couldn\'t add user group.Try again')
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

        if (!$userGroup) {
            return response()->json([
                'success' => false,
                'error' => array('message' => 'User group not found. Try again')
            ], 404);
        }

        $userGroup->fill($params);

        if ($userGroup->save()) {
            return response()->json([
                'success' => true,
                'message' => 'User Group updated successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message' => 'Couldn\'t update user group.Try again')
        ], 404);
    }

    private function createDefaultButtons($groupId)
    {
        //Todo: Add to database as meta table and read from it or copy from default Group
        $buttons = array(
            [
                'evolution' => 1,
                'button_label' => 'Problem with me E1B1',
                'cause1' => 'Short Fused',
                'cause2' => 'Over Caring',
                'cause3' => 'Highly Concerned',
                'cause4' => 'Absent minded',
                'cause5' => 'Angry',
                'node' => 'E1B1',
                'branch1' => 'E2B1',
                'branch2' => 'E2B2',
            ],
            [
                'evolution' => 1,
                'button_label' => 'Problem with world E1B2',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E1B2',
                'branch1' => 'E2B3',
                'branch2' => 'E2B4',
            ],
            [
                'evolution' => 2,
                'button_label' => 'Problem with world E2B1',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E2B1',
                'branch1' => 'E3B1',
                'branch2' => 'E3B2',
            ],
            [
                'evolution' => 2,
                'button_label' => 'Problem with world E2B2',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E2B2',
                'branch1' => 'E3B3',
                'branch2' => 'E3B4',
            ],
            [
                'evolution' => 2,
                'button_label' => 'Problem with world E2B3',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E2B3',
                'branch1' => 'E3B5',
                'branch2' => 'E3B6',
            ],
            [
                'evolution' => 2,
                'button_label' => 'Problem with world E2B4',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E2B4',
                'branch1' => 'E3B7',
                'branch2' => 'E3B8',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B1',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B1',
                'branch1' => 'E4B1',
                'branch2' => 'E4B2',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B2',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B2',
                'branch1' => 'E4B3',
                'branch2' => 'E4B4',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B3',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B3',
                'branch1' => 'E4B5',
                'branch2' => 'E4B6',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B4',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B4',
                'branch1' => 'E4B7',
                'branch2' => 'E4B8',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B5',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B5',
                'branch1' => 'E4B9',
                'branch2' => 'E4B10',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B6',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B6',
                'branch1' => 'E4B11',
                'branch2' => 'E4B12',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B7',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B7',
                'branch1' => 'E4B13',
                'branch2' => 'E4B14',
            ],
            [
                'evolution' => 3,
                'button_label' => 'Problem with world E3B8',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E3B8',
                'branch1' => 'E4B15',
                'branch2' => 'E4B16',
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B1',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B1',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B2',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B2',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B3',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B3',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B4',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B4',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B5',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B5',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B6',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B6',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B7',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B7',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B8',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B8',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B9',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B9',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B10',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B10',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B11',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B11',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B12',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B12',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B13',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B13',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B14',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B14',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B15',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B15',
                'branch1' => null,
                'branch2' => null,
            ],
            [
                'evolution' => 4,
                'button_label' => 'Problem with world E4B16',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
                'node' => 'E4B16',
                'branch1' => null,
                'branch2' => null,
            ],
        );
        foreach ($buttons as $button) {
            $button['user_group'] = $groupId;
            Buttons::create($button);
        }
        return true;
    }

}