<?php

namespace App\Http\Controllers;

use App\Tags;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tag;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(["data" => Tags::all()], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'tag_name' => 'required'
            ]);
       
        Tags::create([
            'tag_name'=>$request->tag_name
        ]); 

        return response()->json(['message'=> 'Tags Created Sucessfully'], 200);
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
     * @param  \App\tags  $tags
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tag = Tags::find($id);
        return response()->json(['data'=> $tag], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\tags  $tags
     * @return \Illuminate\Http\Response
     */
    public function edit(Tags $tags)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\tags  $tags
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $request->validate([
            'tag_name' => 'required',
            ]);
        $tags = Tags::find($id);
        $tags->update([
            'tag_name'=>$request->tag_name,
        ]);
        
        return response()->json(['message'=> 'Tags Updated Sucessfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\tags  $tags
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tags = Tags::find($id);
        $tags->delete();
        return response()->json(['message' => "Tags Deleted Successfully"], 200);
    }
}
