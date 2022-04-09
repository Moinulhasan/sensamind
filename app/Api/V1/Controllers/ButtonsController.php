<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\SpecificResourceRequest;
use App\Buttons;
use App\Http\Controllers\Controller;
use App\UserGroups;

class ButtonsController extends Controller
{
    /**
     * List all Buttons
     *
     * @param SpecificResourceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getButtons(SpecificResourceRequest $request)
    {
        $userGroup = UserGroups::find($request->id);
        if($userGroup){
            $buttons = $userGroup->buttons()->get();
            return response()->json([
                'success' => true,
                'buttons' => $buttons
            ]);
        }
        else {
            return response()->json([
                'success' => false,
                'error' => array('message'=>'User Group not found. Try again')
            ]);
        }
    }

    /**
     *
     * Update Button details
     *
     * @param SpecificResourceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateButton(SpecificResourceRequest $request)
    {
        $params = $request->only('button_label','cause1','cause2','cause3','cause4','cause5');
        $button = Buttons::find($request->id);

        if(!$button){
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Button not found. Try again')
            ],404);
        }

        $button->fill($params);

        if($button->save()){
            return response()->json([
                'success' => true,
                'message' => 'Button updated successfully'
            ],200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t update Button. Try again')
        ],404);
    }
}
