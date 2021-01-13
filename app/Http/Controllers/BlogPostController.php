<?php

namespace App\Http\Controllers;

use App\Blog_post;
use Cloudder;
use Illuminate\Http\Request;
use Validator;

class blogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post = Blog_post::with('blogComments')->get();
        return response()->json(['data'=>$post], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {


        $validate  = Validator::make($request->all(), [
            'post_title' => 'required',
            'post_description' => 'required',
            'category_id' => 'required'
        ]);

        if($validate->fails()){
            return response()->json(['message' => $validate->messages()->first()], 400);
        }
        $image_url = '';
       if($request->has('post_image')){

            $image = $request->file('post_image')->getClientOriginalName();
            $image_name = $request->file('post_image')->getRealPath();
            Cloudder::upload($image_name, null, array("public_id"=>"blog/".uniqid(),
                            "width"=>600, "height"=>600,"sign_url" => true, "fetch_format"=>'auto', "quality"=>"auto"));

            $image_url= Cloudder::secureShow(Cloudder::getResult()["secure_url"]);

       }
        Blog_post::create([
            'post_title'=>$request->post_title,
            'post_description' => $request->post_description,
            'category_id' => $request->category_id,
            'youtube' => $request->youtube,
            'post_image' => $image_url,
        ]);

        return response()->json(['message'=> 'Post Created Sucessfully'], 200);

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Blog_post = Blog_post::find($id);
        return response()->json(['data'=>$Blog_post], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePostImage(Request $request, Blog_post $Blog_post)
    {
        $image = $request->file('post_image')->getClientOriginalName();
        $image_name = $request->file('post_image')->getRealPath();
        Cloudder::upload($image_name, null, array("public_id"=>"blog/".uniqid(),
                        "width"=>600, "height"=>600, "sign_url" => true, "fetch_format"=>'auto', "quality"=>"auto"));

        $image_url= Cloudder::secureShow(Cloudder::getResult()["secure_url"]);
        $post_image = ['post_image' => $image_url];

        $Blog_post->update(['post_image' => $image_url]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate  = Validator::make($request->all(), [
            'post_title' => 'required',
            'post_description' => 'required',
            'category_id' => 'required'
        ]);

        if($validate->fails()){
            return response()->json(['message' => $validate->messages()->first()], 400);
        }
        $Blog_post =  Blog_post::find($id);
        $image_url = $Blog_post->post_image;

       if($request->has('post_image')){

            $image = $request->file('post_image')->getClientOriginalName();
            $image_name = $request->file('post_image')->getRealPath();
            Cloudder::upload($image_name, null, array("public_id"=>"blog/".uniqid(),
                            "width"=>600, "height"=>600, "sign_url" => true, "fetch_format"=>'auto', "quality"=>"auto"));

            $image_url= Cloudder::secureShow(Cloudder::getResult()["secure_url"]);

       }
       $Blog_post->update([
            'post_title'=>$request->post_title,
            'post_description' => $request->post_description,
            'youtube' => $request->youtube,
            'category_id' => $request->category_id,
            'post_image' => $image_url,
        ]);
        return response()->json(['message'=> 'Post Updated Sucessfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Blog_post = Blog_post::find($id);
        $Blog_post->delete();
        return response()->json(['message'=> 'Post Deleted Sucessfully'], 200);
    }
}
