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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentSet()
    {
        return $this->belongsTo(Labels::class,'current_set');
    }

    public function nextSet()
    {
        return $this->belongsTo(Labels::class,'next_set');
    }
}
