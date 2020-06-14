<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class CreateLabelRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.create_label.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
