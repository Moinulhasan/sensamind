<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evolutions extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','current_set','next_set','gender','argued'];
    protected $hidden = ['created_at','updated_at'];
    protected $with = ['buttonOne','buttonTwo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buttonOne()
    {
        return $this->belongsTo(Labels::class,'button_1');
    }

    public function buttonTwo()
    {
        return $this->belongsTo(Labels::class,'button_2');
    }
}
