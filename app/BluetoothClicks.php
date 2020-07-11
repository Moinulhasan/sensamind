<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BluetoothClicks extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'tmp_bluetooth_clicks';
    protected $fillable = ['user_id','evolution','button','clicked_at'];
    protected $hidden = ['created_at','updated_at'];
    protected $dates = ['clicked_at'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
