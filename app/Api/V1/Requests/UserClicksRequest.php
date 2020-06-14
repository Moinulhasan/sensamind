<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class UserClicksRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.get_user_clicks.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
