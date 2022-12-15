<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $guarded;

    public function sender()
    {
        return $this->belongsTo(User::class,'user_one','id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class,'user_two','id');
    }
}
