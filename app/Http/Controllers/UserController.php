<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Auth;
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
        $user->assignRole('show posts');

        return response()->json(["message"=>"User Created Successfully"],201);
    }

    public function me()
    {
            $user = Auth::user();
            return response()->json(['data'=> $user], 200);
    }

    public function getAuthenticatedUser()
        {
                try {

                        if (! $user = JWTAuth::parseToken()->authenticate()) {
                                return response()->json(['user_not_found'], 404);
                        }

                } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                        return response()->json(['token_expired'], $e->getStatusCode());

                } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                        return response()->json(['token_invalid'], $e->getStatusCode());

                } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                        return response()->json(['token_absent'], $e->getStatusCode());

                }

                return response()->json(compact('user'));
        }
}