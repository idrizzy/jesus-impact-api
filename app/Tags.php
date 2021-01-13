<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $fillable = ['tag_name'];

    public function blogPosts(){
        return $this->belongsToMany(Blog_post::class);
    }

}
