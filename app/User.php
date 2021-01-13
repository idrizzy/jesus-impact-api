<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Overtrue\LaravelFollow\Followable;
use Overtrue\LaravelLike\Traits\Liker;

class User extends Authenticatable implements JWTSubject
{
        use Notifiable, HasRoles;
        use Followable, Liker;
        protected $guard_name = 'api';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['name', 'email','username', 'password','phone','photo','gender','dob','country_id', 'status'];

    public function feeds()
    {
        return $this->hasMany('App\Models\Feed');
    }

    public function communities()
    {
        return $this->belongsToMany('App\Models\Community', 'community_user', 'user_id', 'community_id')->withPivot('status');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role(){
        return $this->belongsTo('App\Role');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime',];
}
