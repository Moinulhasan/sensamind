<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'receiver_id' => 'required|integer',
            'message' => 'required|string',
            'attachment'=>'nullable|mimes:jpeg,png,gif,svg,jpg,mp4,mov,ogg,qt|max:100000'
        ];
    }
}
