<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogComment extends Model
{
    protected $fillable = [
        'blog_post_id', 'comment'
    ];

    public function blogPost(){
        return $this->BelongsTo(Blog_post::class);
    }
}
