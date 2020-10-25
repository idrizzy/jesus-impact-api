<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\File;
use App\User;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Cloudder;
use App\Http\Traits\NewNotificationTrait;
use App\Models\Device;

class FeedController extends Controller
{
    use NewNotificationTrait;

    public function show($id)
    {
        $user = User::find(Auth::id());
        $following = array_flip($user->followings->pluck('id')->toArray());
        $followers = array_flip($user->followers->pluck('id')->toArray());
        $following[$user->id] = $user->id;
        $newFriendArray = array_replace($followers,$following);
        $ids = collect($newFriendArray)->keys()->all();
        $feeds = Feed::with('likes')
                     ->with('likers')
                     ->with('feeds')
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at','feed_id','feedPostType')
                     ->where('id',$id)
                     ->first();
        return response()->json(['data'=> $feeds], 200);
    }

    public function index()
    {
        $user = User::find(Auth::id());
        $following = array_flip($user->followings->pluck('id')->toArray());
        $followers = array_flip($user->followers->pluck('id')->toArray());
        $following[$user->id] = $user->id;
        $newFriendArray = array_replace($followers,$following);
        $ids = collect($newFriendArray)->keys()->all();
        $feeds = Feed::with('likes')
                     ->with('likers')
                     ->with('feeds')
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at','feed_id','feedPostType')
                     ->whereIn('user_id', $ids)
                     ->latest()->get();
        return response()->json(['data'=> $feeds], 200);
    }

    public function myFeeds()
    {
        $user = User::find(Auth::id());
        $feeds = Feed::with('likes')
                     ->with('likers')
                     ->with('feeds')
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at','feed_id','feedPostType')
                     ->where('user_id', $user->id)
                     ->latest()->get();
        return response()->json(['data'=> $feeds], 200);
    }

    public function userFeeds($id)
    {
        $user = User::find($id);
        if ($user) {
            $feeds = Feed::with('likes')
                         ->with('likers')
                         ->with('feeds')
                         ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                         ->with('files')
                         ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                         ->select('id','user_id','postType','content','created_at','feed_id','feedPostType')
                         ->where('user_id', $id)
                         ->latest()->get();
            $followers = ($user->followers) ? $user->followers : [];
            $followings = ($user->followings) ? $user->followings : [];
            $currentUser = Auth::user();
            $isFollowed = $user->isFollowedBy($currentUser);
            return response()->json(['data'=> $feeds,'followers'=>$followers, 'followings'=>$followings,'user'=>$user,'followStatus'=> $isFollowed], 200);
        }
        else{
            return response()->json(['data'=> 'User not found'], 404);
        }
    }

    public function toggleLike(Request $request)
    {
        $user = User::find($request->user_id);
        $feed = Feed::find($request->feed_id);
        $user->toggleLike($feed);
        return response()->json(['message'=> 'ok'],200);
    }
    public function like(Request $request)
    {
        $user = User::find($request->user_id);
        $feed = Feed::find($request->feed_id);
        $user->like($feed);
        if ($request->user_id != $feed->user_id) {
            $userid = Auth::id();
            $receiver_id = $feed->user_id;
            $action_id = $request->feed_id;
            $content = $user->username.' liked your post';
            $type = 'post';
            $notificationTime = date('h:i a');
            $this->saveNotification($userid, $receiver_id, $action_id, $content, $notificationTime, $type);
            $device = Device::where('user_id',$receiver_id)->first()->device;
            return response()->json(['message'=> 'ok', 'device' => $device, 'content' => $content],200);
        }
        return response()->json(['message'=> 'ok', ],200);
    }
    public function unLike(Request $request)
    {
        $user = User::find($request->user_id);
        $feed = Feed::find($request->feed_id);
        $user->unlike($feed);
        return response()->json(['message'=> 'ok'],200);
    }

    public function store(Request $request)
    {
        // return response()->json(['message' => $request->all()], 200);
        $validate  = Validator::make($request->all(), [
            'postType' => ['required', 'string'],
            'feedType' => ['required', 'string'],
        ]);

        if($validate->fails()){
            return response()->json(['message' => $validate->messages()->first()], 400);
        }
        $data = $request->only(['content','postType','feedType']);
        $data['user_id'] = Auth::id();
        if ($request->feedType != 'personal') {
            $data['community_id'] = $request->community_id;
        }

        if ($request->feedPostType == 'shared') {
            $data['feedPostType'] = 'shared';
            $data['feed_id'] = $request->feed_id;
        }

        $feed = new Feed($data);
        $save = $feed->save();
        if ($save) {
            if ($request->postType == 'image') {
                if ($request->hasFile('filename')){
                    $picture = $request->file('filename');
                    if (is_array($picture)) {
                        $pictures = [];
                        $image_urls = [];
                        foreach ($picture as $p) {
                            $pictures[] = $p;
                            $upload = Cloudder::upload($p, null,array("public_id" => "feed/".uniqid(), "width"=>600, "height"=>600, "crop"=>"imagga_scale","sign_url" => true, "fetch_format"=>"auto","quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));

                            if (!$upload) {
                                return response()->json(['message'=>'Unable to upload file!!! Check  and try again'], 400);
                            }
                            $image_urls[] =Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                        }
                        foreach ($image_urls as $image) {
                            $picturesss = File::create(['filename' => $image]);
                        }
                        $picfirstid =  $picturesss->id;
                        if (count($picture) > 1) {
                            for ($i = 0; $i <= count($picture) - 1; $i++) {
                                $numss[] = $picfirstid - (10 * $i);
                            }
                            $feed->files()->attach($numss);
                        }
                        else{
                            $feed->files()->attach($picfirstid);
                        }
                    }
                }
            }
            elseif ($request->postType == 'document') {
                if ($request->hasFile('filename')){
                    $picture = $request->file('filename');
                    if (is_array($picture)) {
                        $pictures = [];
                        $image_urls = [];
                        foreach ($picture as $p) {
                            $pictures[] = $p;
                            $upload = Cloudder::upload($p, null,array("public_id" => "feed/".uniqid()));

                            if (!$upload) {
                                return response()->json(['message'=>'Unable to upload file!!! Check  and try again'], 400);
                            }
                            $image_urls[] =Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                        }
                        foreach ($image_urls as $image) {
                            $picturesss = File::create(['filename' => $image]);
                        }
                        $picfirstid =  $picturesss->id;
                        if (count($picture) > 1) {
                            for ($i = 0; $i <= count($picture) - 1; $i++) {
                                $numss[] = $picfirstid - (10 * $i);
                            }
                            $feed->files()->attach($numss);
                        }
                        else{
                            $feed->files()->attach($picfirstid);
                        }
                    }
                }
            }
            elseif ($request->postType == 'video') {
                if ($request->hasFile('filename')){
                    $video = $request->file('filename');
                    if (is_array($video)) {
                        $videos = [];
                        $video_urls = [];
                        foreach ($video as $p) {
                            $videos[] = $p;
                            $upload = Cloudder::uploadVideo($p, null,array(
                                "resource_type" => "video",
                                "public_id" => "feed/".uniqid(),
                                "chunk_size" => 6000000,
                                "eager" => array(
                                array("width" => 300, "height" => 300, "crop" => "pad", "audio_codec" => "none"),
                                array("width" => 160, "height" => 100, "crop" => "crop", "gravity" => "south", "audio_codec" => "none")
                                ),
                                "eager_async" => TRUE)
                            );
                            if (!$upload) {
                                return response()->json(['message'=>'Unable to upload file!!! Check  and try again'], 400);
                            }
                            $video_urls[] =Cloudder::secureShow(Cloudder::getResult()['secure_url']);
                        }
                        foreach ($video_urls as $video) {
                            $videosss = File::create(['filename' => $video]);
                        }
                        $picfirstid =  $videosss->id;
                        if (count($videos) > 1) {
                            for ($i = 0; $i <= count($video) - 1; $i++) {
                                $numss[] = $picfirstid - (10 * $i);
                            }
                            $feed->files()->attach($numss);
                        }
                        else{
                            $feed->files()->attach($picfirstid);
                        }
                    }
                }
            }
            if ($request->feedPostType == 'shared') {
                return response()->json(['status' => 'ok', 'message'=>'Feed Shared Successfully!'], 201);
            }
            return response()->json(['status' => 'ok', 'message'=>'Feed Created Successfully!'], 201);
        }
    }


    public function destroy($feed)
    {
        $getUserFeed = Feed::where('user_id',Auth::id())->where('id',$feed)->first();
        if ($getUserFeed) {
            Feed::where('id',$feed)->delete();
            return response()->json(['message'=> 'Feed Deleted Successfully'], 200);
        }
        return response()->json(['message'=> 'You are not allowed to delete this feed'], 403);
    }
}
