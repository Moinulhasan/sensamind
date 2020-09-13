<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserClicks extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','user_group','evolution','button_id','button','cause','additional_info','clicked_at'];
    protected $hidden = ['created_at','updated_at'];
    protected $dates = ['clicked_at'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
