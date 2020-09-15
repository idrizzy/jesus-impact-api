<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Community extends Model
{

    protected $fillable = ['name','description','image','category'];
    
    public function users()
    {
        return $this->belongsToMany('App\User', 'community_user', 'community_id', 'user_id');
    }

    public function feeds()
    {
        return $this->hasMany('App\Models\Feed', 'community_id', 'id');
    }
}
