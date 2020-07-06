<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.create_user.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
