<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\CreateLabelRequest;
use App\Api\V1\Requests\UpdateLabelRequest;
use App\Labels;
use App\Http\Controllers\Controller;

class LabelsController extends Controller
{
    /**
     * List all labels
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLabels()
    {
        $labels = Labels::all();

        return response()->json([
            'success' => true,
            'labels' => $labels
        ]);
    }

    /**
     *
     * Create label
     *
     *  @return \Illuminate\Http\JsonResponse
     */

    public function createLabel(CreateLabelRequest $request)
    {
        $labels = new Labels($request->all());
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
     *  @return \Illuminate\Http\JsonResponse
     */
    public function updateLabel(UpdateLabelRequest $request)
    {
        $params = $request->only('title','button1','button2','cause1','cause2','cause3','cause4','cause5');
        $label = Labels::find($request->id);
        
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
