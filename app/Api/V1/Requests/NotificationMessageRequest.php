<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class NotificationMessageRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.notification_message.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
