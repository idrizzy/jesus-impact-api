<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Auth;
class DeviceController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function show(Device $device)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $device)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device)
    {
        //
    }
}
