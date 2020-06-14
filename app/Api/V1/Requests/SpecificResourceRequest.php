<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class SpecificResourceRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.by_id.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
