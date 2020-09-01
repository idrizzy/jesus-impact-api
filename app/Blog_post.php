<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blog_post extends Model
{
    protected $fillable = [
        'post_title', 'post_description', 'post_image', 'category_id'
    ];

    public function category(){
        return $this->hasOne(Category::class);
    }

    public function tags(){
        $this->belongsToMany(Tags::class);
    }

    public function blogComment(){
        return $this->hasMany(BlogComment::class);
    }

    
}
