<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\File;
use App\User;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Cloudder;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at')
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
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at')
                     ->whereIn('user_id', $ids)
                     ->latest()->get();
        return response()->json(['data'=> $feeds], 200);
    }

    public function myFeeds()
    {
        $user = User::find(Auth::id());
        $feeds = Feed::with('likes')
                     ->with('likers')
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at')
                     ->where('user_id', $user->id)
                     ->latest()->get();
        return response()->json(['data'=> $feeds], 200);
    }

    public function userFeeds($id)
    {
        $user = User::find($id);
        $feeds = Feed::with('likes')
                     ->with('likers')
                     ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
                     ->with('files')
                     ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }))
                     ->select('id','user_id','postType','content','created_at')
                     ->where('user_id', $id)
                     ->latest()->get();
        $followers = $user->followers;
        $followings = $user->followings;
        return response()->json(['data'=> $feeds,'followers'=>$followers, 'followings'=>$followings,'user'=>$user], 200);
    }

    public function toggleLike(Request $request)
    {
        $user = User::find($request->user_id);
        $feed = Feed::find($request->feed_id);
        $user->toggleLike($feed);
        return response()->json(['message'=> 'ok'],200);
    }

    public function store(Request $request)
    {
        // return response()->json(['message' => $request->all()], 200);
        $validate  = Validator::make($request->all(), [
            'postType' => ['required', 'string'],
        ]);

        if($validate->fails()){
            return response()->json(['message' => $validate->messages()->first()], 400);
        }
        $data = $request->only(['content','postType']);
        $data['user_id'] = Auth::id();

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
                            $upload = Cloudder::upload($p, null,array("public_id" => "feed/".uniqid(), "width"=>600, "height"=>600, "crop"=>"scale", "fetch_format"=>"auto","quality"=>"auto",  "flags"=>array("progressive", "progressive:semi", "progressive:steep")));

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
                            $upload = Cloudder::upload($p, null,array(
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
            return response()->json(['status' => 'ok', 'message'=>'Feed Created Successfully!'], 201);
        }
    }


    public function destroy($feed)
    {
        $getUserFeed = Feed::where('user_id',Auth::id())->where('id',$feed)->first();
        if ($getUserFeed) {
            Feed::where('id',$feed)->destroy();
            return response()->json(['message'=> 'Feed Deleted Successfully'], 200);
        }
        return response()->json(['message'=> 'You are not allowed to delete this feed'], 403);
    }
}
