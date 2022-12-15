<?php

namespace App;

use Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\PasswordReset;
use Illuminate\Support\Str;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email','role', 'password','zipcode','age','gender','argued','user_group','current_evolution','current_btn1','current_btn2','evolution_path'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','created_at','updated_at','email_verified_at','failed_logins','lock_out_code','current_btn1','current_btn2'
    ];

    /**
     * The relationships that should be sent along with model
     * @var array
     */

    protected $with = ['userGroup','buttonOne','buttonTwo'];

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }

    public function clicks()
    {
        return $this->hasMany(UserClicks::class);
    }
    public function scopeGetClicksBetween($startDate,$endDate)
    {
        return $this->whereBetween('clicked_at', [$startDate,$endDate]);
    }
    public function bluetoothClicks()
    {
        return $this->hasMany(BluetoothClicks::class);
    }
    public function userGroup()
    {
        return $this->belongsTo(UserGroups::class,'user_group');
    }

    public function buttonOne()
    {
        return $this->belongsTo(Buttons::class,'current_btn1');
    }

    public function buttonTwo()
    {
        return $this->belongsTo(Buttons::class,'current_btn2');
    }

    public function chatGroup()
    {
        return $this->hasMany(ChatGroup::class);
    }

    public function chatRoom()
    {
        return $this->belongsToMany(User::class, 'chat_groups','user_one','user_two')
            ->withPivot('id')
            ->withTimestamps();
    }
}
