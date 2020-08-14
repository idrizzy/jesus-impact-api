<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['filename'];
    public function feeds()
    {
        return $this->hasMany('App\Models\Feed');
    }
}
