<?php

namespace App\Http\Controllers;

use App\Category ;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Auth;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $category = Category::all();
        return response()->json(['data'=>$category], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $request->validate([
                'category_name' => 'required',
                'description' => 'required'
                ]);

            Category::create([
                'category_name'=>$request->category_name,
                'description' => $request->description
            ]);

            return response()->json(['message'=> 'Category Created Sucessfully'], 201);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 403);
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
     * @param  \App\category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cat = Category::with('blogs')->where('id',$id)->get();
        return response()->json(['data'=> $cat], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $request->validate([
                'category_name' => 'required',
                'description' => 'required'
                ]);

            $category = Category::find($id);
            $category->update([
                'category_name'=>$request->category_name,
                'description'=>$request->description,
            ]);

            return response()->json(['message'=> 'Category Updated Sucessfully'], 200);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $category = Category::find($id);
            $category->delete();
            return response()->json(['message'=> 'Category deleted Sucessfully'], 200);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 403);
    }
}
