<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CreateButtonRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Buttons;
use App\Http\Controllers\Controller;

class ButtonsController extends Controller
{
    /**
     * List all Buttons
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getButtons()
    {
        $buttons = Buttons::all();

        return response()->json([
            'success' => true,
            'buttons' => $buttons
        ]);
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
        $params = $request->only('button1','cause1','cause2','cause3','cause4','cause5');
        $label = Buttons::find($request->id);

        if(!$label){
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Button not found. Try again')
            ],404);
        }

        $label->fill($params);

        if($label->save()){
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
