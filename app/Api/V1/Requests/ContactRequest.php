<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.contact_form.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
