<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CreateLabelRequest;
use App\Api\V1\Requests\SpecificResourceRequest;
use App\Buttons;
use App\Http\Controllers\Controller;

class ButtonsController extends Controller
{
    /**
     * List all labels
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLabels()
    {
        $labels = Buttons::all();

        return response()->json([
            'success' => true,
            'labels' => $labels
        ]);
    }

    /**
     *
     * Create label
     *
     * @param CreateLabelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function createLabel(CreateLabelRequest $request)
    {
        $labels = new Buttons($request->all());
        if($labels->save()){
            return response()->json([
                'success' => true,
                'message' => 'Label added successfully'
            ]);
        }
        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t add label.Try again')
        ]);
    }

    /**
     *
     * Update Label details
     *
     * @param SpecificResourceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLabel(SpecificResourceRequest $request)
    {
        $params = $request->only('button1','cause1','cause2','cause3','cause4','cause5');
        $label = Buttons::find($request->id);

        if(!$label){
            return response()->json([
                'success' => false,
                'error' => array('message'=>'Label not found. Try again')
            ],404);
        }

        $label->fill($params);

        if($label->save()){
            return response()->json([
                'success' => true,
                'message' => 'Label updated successfully'
            ],200);
        }

        return response()->json([
            'success' => false,
            'error' => array('message'=>'Couldn\'t update label.Try again')
        ],404);
    }
}
