<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.mailing_list.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
