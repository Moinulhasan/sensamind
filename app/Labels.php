<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Labels extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'button_label', 'cause1', 'cause2', 'cause3', 'cause4', 'cause5'];
    protected $hidden = ['created_at', 'updated_at', 'last_update_by'];

    function evolution()
    {
        return $this->belongsTo(Evolutions::class);
    }
}