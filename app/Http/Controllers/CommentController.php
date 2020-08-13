<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Feed;
use Auth;
class CommentController extends Controller
{
    public function store(Request $request)
    {
        $comment = new Comment;

        $comment->comment = $request->comment;

        $comment->user()->associate(Auth::user());

        $feed = Feed::find($request->feed_id);

        $feed->comments()->save($comment);
    }

    public function replyStore(Request $request)
    {
        $reply = new Comment();

        $reply->comment = $request->get('comment');

        $reply->user()->associate(Auth::user());

        $reply->parent_id = $request->get('comment_id');

        $feed = Feed::find($request->get('feed_id'));

        $feed->comments()->save($reply);

    }
}
