<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    protected $fillable = ['id', 'websocket_host', 'websocket_port', 'websocket_pool_interval', 'db_host', 'db_log_interval'];
}


 