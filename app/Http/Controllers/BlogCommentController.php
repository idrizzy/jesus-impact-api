<?php

namespace App\Http\Controllers;

use App\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
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
    public function create(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'blog_post_id' => 'required'
            ]);
       
        BlogComment::create([
            'comment'=>$request->comment,
            'blog_post_id' => $request->blog_post_id
        ]);
        return response()->json(['message'=> 'Comment Posted Sucessfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\BlogComment  $blogComment
     * @return \Illuminate\Http\Response
     */
    public function show(BlogComment $blogComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BlogComment  $blogComment
     * @return \Illuminate\Http\Response
     */
    public function edit(BlogComment $blogComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BlogComment  $blogComment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BlogComment $blogComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BlogComment  $blogComment
     * @return \Illuminate\Http\Response
     */
    public function destroy(BlogComment $blogComment)
    {
        //
    }
}
