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

class UserController extends Controller
{
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
            return response()->json($validator->errors()->first(), 400);
        }

        $image = $request->file('photo');
        $name = $request->file('photo')->getClientOriginalName();
        $image_name = $request->file('photo')->getRealPath();

        Cloudder::upload($image_name, null, array("public_id"=>"users/".uniqid(),
                        "width"=>600, "height"=>600, "crop"=>'scale', "fetch_format"=>'auto', "quality"=>"auto"));

        $image_url= Cloudder::secureShow(Cloudder::getResult()["secure_url"]);
        User::where('id', Auth::user()->id)->update(['photo'=>$image_url]);

        return response()->json(['message'=>"Profile Photo Updated Successfully"]);

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



    public function getAuthenticatedUser()
    {
            $user = Auth::user();
            $role = $user->roles->pluck('id');
            return response()->json(['data'=> $user], 200);
    }

    // public function getAuthenticatedUser()
    // {
        //         try {

        //                 if (! $user = JWTAuth::parseToken()->authenticate()) {
        //                         return response()->json(['user_not_found'], 404);
        //                 }

        //         } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        //                 return response()->json(['token_expired'], $e->getStatusCode());

        //         } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

        //                 return response()->json(['token_invalid'], $e->getStatusCode());

        //         } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

        //                 return response()->json(['token_absent'], $e->getStatusCode());

        //         }

        //         return response()->json(compact('user'));
    // }
        public function saveImages(Request $request, $image_url)
        {
            $image = new Upload();
            $image->image_name = $request->file('image_name')->getClientOriginalName();
            $image->image_url = $image_url;

            $image->save();
        }
}
