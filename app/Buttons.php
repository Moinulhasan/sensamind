<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Buttons extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_group','evolution', 'button_label', 'cause1', 'cause2', 'cause3', 'cause4', 'cause5'];
    protected $hidden = ['created_at', 'updated_at'];

    function evolution()
    {
        return $this->belongsTo(UserGroups::class);
    }
}