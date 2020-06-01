<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GlobalSettingController extends Controller
{
    public function socket(){
        $data['page_title'] = "Socket Setting";
        $lastData = \App\GlobalSetting::orderBy('id','desc')->first();
        $global_setting = (object) array(
            'id'=> isset($lastData->id) ? $lastData->id : 0, 
            'websocket_host'=> isset($lastData->websocket_host) ? $lastData->websocket_host : null, 
            'websocket_port'=> isset($lastData->websocket_host) ? $lastData->websocket_port : null,
            'websocket_pool_interval'=> isset($lastData->websocket_pool_interval) ? $lastData->websocket_pool_interval : null, 
        );
        $data['global_setting'] = $global_setting ;
        return view('settings.socket',$data);
    }

    public function updateSocket(Request $request,$id = 0){
        if ($id == 0) {
            $global_setting = new \App\GlobalSetting;
        }else{
            $global_setting = \App\GlobalSetting::find($id);

        }

        $global_setting->websocket_host = $request->websocket_host;
        $global_setting->websocket_port = $request->websocket_port;
        $global_setting->websocket_pool_interval = $request->websocket_pool_interval;
        $global_setting->save();

        return redirect()->back()->with(['create' => 'Data saved successfully!']);
        
    }


    public function database()
    {
        $data['page_title'] = "Database Setting";

        $lastData = \App\GlobalSetting::orderBy('id','desc')->first();
        
        $data['id'] = isset($lastData->id) ? $lastData->id : 0;
        $data['db_host'] = isset($lastData->db_host) ? $lastData->db_host : null;
        $data['db_log_interval'] = isset($lastData->db_log_interval) ? $lastData->db_log_interval : null;
        return view('settings.database', $data);
    }

    public function updateDatabase(Request $request, $id = 0)
    {
        if ($id == 0) {
            $global_setting = new \App\GlobalSetting;
        } else {
            $global_setting = \App\GlobalSetting::find($id);
        }   

        $global_setting->db_host = $request->db_host;
        $global_setting->db_log_interval = $request->db_log_interval;
        $global_setting->save();

        return redirect()->back()->with(['create' => 'Data saved successfully!']);
    }



   
}
