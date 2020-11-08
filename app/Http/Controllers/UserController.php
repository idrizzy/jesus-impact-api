<?php

namespace App\Http\Controllers;
use Cloudder;
use App\Rules\MatchOldPassword;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Auth;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Exceptions\JWTException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Http\Traits\NewNotificationTrait;
use App\Models\Device;

class UserController extends Controller
{
    use NewNotificationTrait;
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function socialRegister(Request $request)
    {
        $exist = User::where('email', $request->email)->first();
        if ($exist) {
            $token = auth()->login($exist);
            return response()->json(['data'=> $token], 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->first(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('email')),
            'username'=>$request->get('name').rand(1,100),
            'photo'=>$request->get('photo')
        ]);
        Device::create([
            'user_id' => $user->id,
            'device' => $request->get('device'),
            'device_type' => ($request->get('device_type'))? $request->get('device_type') : 'mobile'
        ]);
        $user->assignRole('Users');

        $token = auth()->login($user);
        return response()->json(['data'=> $token], 200);
    }
    
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = Auth::guard()->attempt($credentials)) {
                return response()->json(['error' => 'invalid credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(['data'=> $token], 200);
    }

    public function register(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->first(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'username'=>$request->get('username')
        ]);
        Device::create([
            'user_id' => $user->id,
            'device' => $request->get('device'),
            'device_type' => ($request->get('device_type'))? $request->get('device_type') : 'mobile'
        ]);
        $user->assignRole('Users');

        return response()->json(["message"=>"Account Created Successfully"],201);
    }

    public function test(){
        $user = User::where('id', 1)->first();
        return $user->getPermissionsViaRoles();
    }

    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email'
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->first(), 400);
        }

        $user = User::where(['id' => Auth::user()->id]);
        $user->update([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'username'=>$request->get('username'),
            'phone'=>$request->get('phone'),
            'dob'=>$request->get('dob'),
            'country_id'=>$request->get('country_id'),
        ]);
        // $user->assignRole('show posts');
        return response()->json(["message"=>"Account Updated Successfully"],201);
    }

    public function UpdateProfilePicture(Request $request){

        $validator = Validator::make($request->all(), [
            'photo'=>'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);

        if($validator->fails()){
            return response()->json( ['message'=> $validator->errors()->first()], 400);
        }

        $image = $request->file('photo');
        $name = $request->file('photo')->getClientOriginalName();
        $image_name = $request->file('photo')->getRealPath();

        Cloudder::upload($image_name, null, array("public_id"=>"users/".uniqid(),
                        "width"=>600, "height"=>600, "crop"=>'imagga_scale', "sign_url" => true, "fetch_format"=>'auto', "quality"=>"auto"));

        $image_url= Cloudder::secureShow(Cloudder::getResult()["secure_url"]);
        User::where('id', Auth::user()->id)->update(['photo'=>$image_url]);

        return response()->json(['message'=>"Profile Photo Updated Successfully"],201);

    }

    public function changePassword(Request $request)
    {
        $ss = new MatchOldPassword;

        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
        if($ss->passes($request->new_password, $request->current_password))
            return response()->json(["message"=>"Password Does Not Match The Old one"],422);

        $user = User::where(['id' => $request->id]);
        $user->update(['password'=> Hash::make($request->new_password)]);
        return response()->json(["message"=>"User Password Changed Successfully"],201);
    }

    public function toggleFollow(Request $request)
    {
        $user = User::find($request->user_id);
        $currentUser = User::find(Auth::id());
        $currentUser->toggleFollow($user);
        return response()->json(['message'=> 'ok'],200);
    }

    public function follow(Request $request)
    {
        $user = User::find($request->user_id);
        $currentUser = User::find(Auth::id());
        $currentUser->follow($user);

        $userid = Auth::id();
        $receiver_id = $request->user_id;
        $action_id = $userid;
        $content = $currentUser->username.' follows you';
        $type = 'follow';
        $notificationTime = date('h:i a');
        $this->saveNotification($userid, $receiver_id, $action_id, $content, $notificationTime, $type);
        $device = Device::where('user_id',$receiver_id)->first()->device;
        return response()->json(['message'=> 'ok', 'device' => $device, 'content' => $content],200);
    }

    public function unFollow(Request $request)
    {
        $user = User::find($request->user_id);
        $currentUser = User::find(Auth::id());
        $currentUser->unfollow($user);

        $userid = Auth::id();
        $receiver_id = $request->user_id;
        $action_id = $userid;
        $content = $currentUser->username.' un-followed you';
        $type = 'follow';
        $notificationTime = date('h:i a');
        $this->saveNotification($userid, $receiver_id, $action_id, $content, $notificationTime, $type);
        $device = Device::where('user_id',$receiver_id)->first()->device;
        return response()->json(['message'=> 'ok', 'device' => $device, 'content' => $content],200);
    }

    public function users(Request $request)
    {
        if ($request->search) {
            $search = $request->search;
            $allRandomUsers =  User::where('username', 'like', '%' . $search . '%')->orWhere('name', 'like', '%' . $search . '%')->orderByRaw('RAND()')->take(50)->get();
            return response()->json(['data'=> $allRandomUsers],200);
        }
        $user = User::find(Auth::id());
        $following = array_flip($user->followings->pluck('id')->toArray());
        $following[$user->id] = $user->id;
        $ids = collect($following)->keys()->all();
        $allRandomUsers =  User::whereNotIn('id',$ids)->orderByRaw('RAND()')->take(15)->get();
        return response()->json(['data'=> $allRandomUsers],200);
    }
    public function getAuthenticatedUser()
    {
            $user = Auth::user();
            $role = $user->roles->pluck('id');
            return response()->json(['data'=> $user,'followers'=>$user->followers()->count(),'followings'=>$user->followings()->count()], 200);
    }

    public function followings()
    {
        $user = Auth::user();
        return response()->json(['data'=> $user->followings], 200);
    }

    public function followers()
    {
        $user = Auth::user();
        return response()->json(['data'=> $user->followers], 200);
    }

    public function saveImages(Request $request, $image_url)
    {
        $image = new Upload();
        $image->image_name = $request->file('image_name')->getClientOriginalName();
        $image->image_url = $image_url;

            $image->save();
    }

    public function banUser(Request $request){
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $ban = User::where('id', $request->id);
            $ban->update([
                'status' => 'inactive'
            ]);
            return response()->json(['message'=>'User Banned Successfully'], 200);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 403);
    }

    public function unBanUser(Request $request){
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $unban = User::where('id', $request->id);
            $unban->update([
                'status' => 'active'
            ]);
            return response()->json(['message'=>'User Activated Successfully'], 200);
        }
        return response()->json(['message'=>'User Does not have permissions to perfrom this operation'], 403);

    }

    public function activeUsers(){
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $data = User::where('status', 'active')->get();
            return response()->json(['data'=>$data], 200);
        }
        return response()->json(['data'=>'User Does not have permissions to perfrom this operation'], 403);
    }

    public function inActiveUsers(){
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $data = User::where('status', 'inactive')->get();
            return response()->json(['data'=>$data], 200);
        }
        return response()->json(['data'=>'User Does not have permissions to perfrom this operation'], 403);
    }

    public function allUsers(){
        $user = Auth::user();
        if($user->roles[0]->name == 'SuperAdmin'){
            $data = User::all();
            return response()->json(['data'=>$data], 200);
        }
        return response()->json(['data'=>'User Does not have permissions to perfrom this operation'], 403);
    }
}
