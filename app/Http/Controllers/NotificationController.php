<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
class NotificationController extends Controller
{

    public function today()
    {
        $notifictaions = Notification::where('receiver_id',Auth::id())->whereDate('created_at', '=', Carbon::today()->toDateString())->get();
        return response()->json(['data'=>  $notifictaions],200);
    }

    public function yesterday()
    {
        $notifictaions = Notification::where('receiver_id',Auth::id())->whereDate('created_at', '=', Carbon::now()->subdays(1)->toDateString())->get();
        return response()->json(['data'=>  $notifictaions],200);
    }

}
