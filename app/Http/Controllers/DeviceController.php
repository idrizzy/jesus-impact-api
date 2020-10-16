<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Auth;
use Validator;
class DeviceController extends Controller
{

    public function store(Request $request)
    {
        return response()->json(['message' => $request->all()], 200);
        $validate  = Validator::make($request->all(), [
            'device' => ['required']
        ]);

        if($validate->fails()){
            return response()->json(['message' => $validate->messages()->first()], 400);
        }
        $getExist = Device::where('user_id', Auth::id())->first();
        if ($getExist) {
            Device::where('user_id', Auth::id())->update([
                'user_id' => Auth::id(),
                'device' => $request->device,
                'device_type' => ($request->device_type)? $request->device_type : 'mobile'
            ]);
        }else{
            Device::create([
                'user_id' => Auth::id(),
                'device' => $request->device,
                'device_type' => ($request->device_type)? $request->device_type : 'mobile'
            ]);
        }
    }


}
