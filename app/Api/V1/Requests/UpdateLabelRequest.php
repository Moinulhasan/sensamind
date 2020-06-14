<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class UpdateLabelRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.update_label.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
