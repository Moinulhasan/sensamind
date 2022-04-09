<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class DeviceNotificationTokenRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.notification_token.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
