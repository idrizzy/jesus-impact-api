<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id','content','type','action_id','receiver_id','notificationTime'];

}
