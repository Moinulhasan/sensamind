<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;

class AdminRequest extends FormRequest
{
    public function rules()
    {
        return [];
    }

    public function authorize()
    {
        return true;
    }
}
