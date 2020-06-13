<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class VerifyAccountRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.verify_account.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
