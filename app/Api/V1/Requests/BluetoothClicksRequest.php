<?php

namespace App\Api\V1\Requests;

use Config;
use Dingo\Api\Http\FormRequest;

class BluetoothClicksRequest extends FormRequest
{
    public function rules()
    {
        return Config::get('boilerplate.bluetooth_clicks.validation_rules');
    }

    public function authorize()
    {
        return true;
    }
}
