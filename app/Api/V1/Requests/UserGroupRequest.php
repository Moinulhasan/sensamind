<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class UserGroupRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.create_user_group.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
