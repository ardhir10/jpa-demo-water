<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sensor;
use App\Tag;
use Illuminate\Support\Facades\Validator;
use Exception;
class SensorController extends Controller
{
    public function index()
    {

        $data['page_title'] = 'Sensors Setting';

        $data['sensors'] = Sensor::orderBy('id','desc')->with(['device','tag'])->get();
        $data['tags'] = Tag::orderBy('id','desc')->with(['device'])->get();
            
        return view('settings.sensors.index', $data);
    }


    public function active(){
        $sensors = Sensor::orderBy('id', 'asc')->whereSensorStatus(1)->get();
        return json_encode($sensors);
    }


    public function update(Request $request, $id)
    {
        try {
            $messages = [
                'unique' => ' :attribute already taken.',
            ];
            

            $gateway_id = \App\Tag::find($request->tag_id)->device_controller_id;
            $request->request->add(['gateway_id'=> $gateway_id ]);
            $dataTag = $request->input();
            
            
            $validation = Validator::make($dataTag, [
                'sensor_display'        => ['required'],
                'tag_id'                => ['required'],
                'sensor_status'         => ['required'],
            ], $messages);

            $errors = $validation->errors();
            if (count($errors) > 0) {
                throw new Exception($errors->toJson());
            }
            Sensor::whereId($id)->update($dataTag);

            return response()->json(['status' => '200']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['status' => '403', 'msg' => $th->getMessage()]);
        }
    }
}
