<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DataController extends Controller
{
        public function open() 
        {
            $data = "This data is open and can be accessed without the client being authenticated";
            return response()->json(compact('data'),200);

        }

        public function closed() 
        {
          return Auth::user();
            $data = "Only authorized users can see this";
            return response()->json(compact('data'),200);
        }
}