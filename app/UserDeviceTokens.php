<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeviceTokens extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['device_id','type','registration_id'];
    protected $hidden = ['created_at','updated_at'];
}
