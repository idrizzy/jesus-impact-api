<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\File;
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
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate  = Validator::make($request->all(), [
            'postType' => ['required', 'string'],
            'content' => ['required', 'string'],
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\Response
     */
    public function show(Feed $feed)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\Response
     */
    public function edit(Feed $feed)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feed $feed)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feed $feed)
    {
        //
    }
}
