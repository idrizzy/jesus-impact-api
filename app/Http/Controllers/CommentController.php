<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Feed;
use Auth;
use App\Http\Traits\NewNotificationTrait;
use App\Models\Device;
class CommentController extends Controller
{
    use NewNotificationTrait;

    public function store(Request $request)
    {
        $comment = new Comment;

        $comment->comment = $request->comment;

        $comment->user()->associate(Auth::user());

        $feed = Feed::find($request->feed_id);

        $feed->comments()->save($comment);
        if (Auth::id() != $feed->user_id) {
            $userid = Auth::id();
            $receiver_id = $feed->user_id;
            $action_id = $request->feed_id;
            $content = Auth::user()->username.' commented on your feed';
            $type = 'post';
            $notificationTime = date('h:i a');
            $this->saveNotification($userid, $receiver_id, $action_id, $content, $notificationTime, $type);
            $device = Device::where('user_id',$receiver_id)->first()->device;
            return response()->json(['message'=> 'ok', 'device' => $device, 'content' => $content],200);

        }

    }

    public function replyStore(Request $request)
    {
        $reply = new Comment();

        $reply->comment = $request->get('comment');

        $reply->user()->associate(Auth::user());

        $reply->parent_id = $request->get('comment_id');

        $feed = Feed::find($request->get('feed_id'));

        $feed->comments()->save($reply);

        $getComment = Comment::where('parent_id', $request->get('comment_id'))->first();
        if (Auth::id() != $getComment->user_id) {
            $userid = Auth::id();
            $receiver_id = $getComment->user_id;
            $action_id = $request->feed_id;
            $content = Auth::user()->username.' replied to your comment';
            $type = 'post';
            $notificationTime = date('h:i a');
            $this->saveNotification($userid, $receiver_id, $action_id, $content, $notificationTime, $type);
            $device = Device::where('user_id',$receiver_id)->first()->device;
            return response()->json(['message'=> 'ok', 'device' => $device, 'content' => $content],200);

        }

    }
}
