<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Auth;
class DeviceController extends Controller
{
   
    public function store(Request $request)
    {
        $getExist = Device::where('user_id', Auth::id())->first();
        if ($getExist) {
            Device::where('user_id', Auth::id())->update([
                'user_id' => Auth::id(),
                'device' => $request->get('device'),
                'device_type' => ($request->get('device_type'))? $request->get('device_type') : 'mobile'
            ]);
        }else{
            Device::create([
                'user_id' => Auth::id(),
                'device' => $request->get('device'),
                'device_type' => ($request->get('device_type'))? $request->get('device_type') : 'mobile'
            ]);
        }
    }

   
}
