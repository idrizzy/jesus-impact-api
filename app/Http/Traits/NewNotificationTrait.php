<?php

namespace App\Http\Traits;
use App\Models\Notification;

trait NewNotificationTrait {
    public function saveNotification($userid,$receiver_id,$action_id,$content,$notificationTime,$type) {
  
      $data['user_id'] = $userid;
      $data['receiver_id'] = $receiver_id;
      $data['action_id'] = $action_id;
      $data['content'] = $content;
      $data['notificationTime'] = $notificationTime;
      $data['type'] = $type;

      $notification = new Notification($data);
      return $notification->save();
  
    }
}