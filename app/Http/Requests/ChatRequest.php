<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'receiver_id' => 'required|integer|' . Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('id', $this->id);
                }),
            'message' => 'required|string',
            'attachment' => 'nullable|mimes:jpeg,png,gif,svg,jpg,mp4,mov,ogg,qt|max:100000'
        ];
    }
}
