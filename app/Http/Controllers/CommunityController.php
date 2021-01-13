<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use Validator;
use Cloudder;
use App\User;
use App\Models\Feed;
use Auth;
use DB;
use App\Http\Traits\NewNotificationTrait;
use App\Models\Device;
class CommunityController extends Controller
{
    use NewNotificationTrait;

    public function communityDetails($id)
    {
        $userid = Auth::id();
        $userCommunities = Community::where('id', $id)->first();
        $checkJoinStatus = DB::table('community_user')->where('community_id', $id)->where('user_id', $userid)->first();
        $joinStatus = (($checkJoinStatus)) ? true : false;
        return response()->json([ "data" =>$userCommunities, "joinStatus" => $joinStatus  ], 200);
    }

    public function unjoinCommunity($id)
    {
        $userid = Auth::id();
        $community = Community::where('id',$id)->first();
        $joined = $community->users()->detach($userid);
        return response()->json([ "message" => 'User left the Community Successfully' ], 200);

    }

    public function joinCommunity($id)
    {
        $userid = Auth::id();
        $checkExist = DB::table('community_user')->where('community_id',$id)->where('user_id', $userid)->first();
        if($checkExist){
            return response()->json([ "message" => 'Already a member of this community' ], 200);

        }
        $community = Community::where('id',$id)->first();
        if ($community->category == 'closed') {
            $joined = $community->users()->attach($userid, ['status'=>'pending']);
            return response()->json([ "message" => 'Community Joined Successfully, Await Confirmation.' ], 200);
        }
        else{

            $joined = $community->users()->attach($userid);
            return response()->json([ "message" => 'Community Joined Successfully' ], 200);
        }

    }

    public function communityFeed($id)
    {
        $communityFeeds = Community::with(array('feeds'=> function($query){
            $query->with('likes')
            ->with('likers')
            ->with(array('user'=> function($query){ $query->select('name','username','id','photo'); }))
            ->with('files')
            ->with(array('comments'=> function($query){ $query->with(array('replies'=> function($query){ $query->with('replies'); })); }));
                }
            )
        )->where('id',$id)->first();
        return response()->json([ "data" => $communityFeeds ], 200);
    }

    public function communityMember($id)
    {
        $communityMember = Community::with('users')->where('id', $id)->get();
        return response()->json([ "data" => $communityMember ], 200);
    }


    public function userCommunities()
    {
        $userid = Auth::id();
        $userCommunities = User::with('communities')->where('id', $userid)->first();
        return response()->json([ "data" => $userCommunities ], 200);
    }

    public function search(Request $request)
    {
        if ($request->search) {
            $search = $request->search;
            $allRandomCommunities =  Community::where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%')->orderByRaw('RAND()')->take(50)->get();
            return response()->json(['data'=> $allRandomCommunities],200);
        }
    }

    public function index()
    {
        $communities = Community::all();
        return response()->json([ "data" => $communities ], 200);
    }

    public function approveCommunityUser(Request $request)
    {
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $getCommunity = Community::where('id', $request->community_id)->first();
            DB::table('community_user')->where('community_id', $request->community_id)->where('user_id',$request->user_id)->update(['status'=>'active']);
            $userid = $request->user_id;
            $receiver_id = $request->user_id;
            $action_id = $request->community_id;
            $content = 'You are now an active member of '.$getCommunity->name;
            $type = 'join';
            $notificationTime = date('h:i a');
            $this->saveNotification($userid, $receiver_id, $action_id, $content, $notificationTime, $type);
            $device = Device::where('user_id',$receiver_id)->first()->device;
            return response()->json([ "message" => "Approved Successfully", 'device' => $device, 'content' => $content ], 200);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 401);
    }

    public function communityDelete($community_id)
    {
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){

            Community::where('id', $community_id)->delete();
            return response()->json([ "message" => "Community Deleted Successfully" ], 200);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 401);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:500',
                'description' => 'required|string|max:500',
                'image' => 'mimes:jpeg,jpg,png,gif|required|max:50000',
                'category' => 'required|string'
            ]);

            if($validator->fails()){
                    return response()->json(["message"=>$validator->errors()->first()], 400);
            }
            $image = $request->file('image');
            $name = $request->file('image')->getClientOriginalName();
            $image_name = $request->file('image')->getRealPath();

            Cloudder::upload($image_name, null, array("public_id"=>"communities/".uniqid(),
                            "width"=>500, "height"=>500, "crop"=>'scale', "fetch_format"=>'auto', "quality"=>"auto"));

            $image_url= Cloudder::secureShow(Cloudder::getResult()["secure_url"]);
            Community::create([
                'name' => $request->get('name'),
                'image' => $image_url,
                'description' => $request->get('description'),
                'category'=>$request->get('category')
            ]);

            return response()->json(["message"=>"Community Created Successfully"],201);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 401);
    }
}
