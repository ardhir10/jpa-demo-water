<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\DB;

class TrendingReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except('trend');
        $this->middleware('privilege:trending_report_view')->except('trend');
    }


    public function index(){
        $data['page_title'] = 'Trending Report';
        $data['sensors'] = \App\Sensor::orderBy('id', 'asc')->whereSensorStatus(1)->get();
        $data['date'] = date('Y-m-d ');

        return view('trending-report.index', $data);
    }

    public function trend(Request $request){
        $daterange = $request->daterange;
        if ($daterange == 'year') {
            $daterange = 'month';
        } elseif ($daterange == 'month') {
            $daterange = 'day';
        } elseif ($daterange == 'day') {
            $daterange = 'hour';
        } elseif ($daterange == 'hour') {
            $daterange = 'minute';
        } else {
            $daterange = 'minute';
        }

        if($request->daterange === 'day'){
            $dataLogs = DB::table('logs')
                ->select(DB::raw("
            date_trunc('" . $daterange . "',tstamp) +
            (((date_part('minute', tstamp)::integer / 5::integer) * 5::integer)
            || 'minutes')::interval AS datetime , 
            avg(ph) as avg_ph,
            avg(tss) as avg_tss,
            avg(amonia) as avg_amonia,
            avg(cod) as avg_cod,
            avg(flow_meter) as avg_flow_meter
            "))
                ->where("tstamp", "LIKE", '%' . $request->date . '%')
                ->groupBy('datetime')
                ->orderBy('datetime', 'asc')
                ->get();
        }else{
            $dataLogs = DB::table('logs')
                ->select(DB::raw("
            date_trunc('" . $daterange . "',tstamp) AS datetime , 
            avg(ph) as avg_ph,
            avg(tss) as avg_tss,
            avg(amonia) as avg_amonia,
            avg(cod) as avg_cod,
            avg(flow_meter) as avg_flow_meter
            "))
                ->where("tstamp", "LIKE", '%' . $request->date . '%')
                ->groupBy('datetime')
                ->orderBy('datetime', 'asc')
                ->get();
        }

        $stackTstamp = [];
        $stack_avg_ph = [];
        $stack_avg_tss = [];
        $stack_avg_amonia = [];
        $stack_avg_cod = [];
        $stack_avg_flow_meter = [];
        

        foreach ($dataLogs as $log) {
            if ($daterange == 'year') {
                array_push($stackTstamp, date('Y', strtotime($log->datetime)));
            } elseif ($daterange == 'month') {
                array_push($stackTstamp, date('Y-m-d', strtotime($log->datetime)));
            } elseif ($daterange == 'day') {
                array_push($stackTstamp, date('Y-m-d', strtotime($log->datetime)));
            } else {
                array_push($stackTstamp, date('Y-m-d H:i:s', strtotime($log->datetime)));
            }
            array_push($stack_avg_ph, ($log->avg_ph === 'NaN' || $log->avg_ph === null) ? "0" : $log->avg_ph);
            array_push($stack_avg_tss, ($log->avg_tss === 'NaN' || $log->avg_tss === null ) ? "0" : $log->avg_tss);
            array_push($stack_avg_amonia, ($log->avg_amonia === 'NaN' || $log->avg_amonia === null) ? "0" : $log->avg_amonia);
            array_push($stack_avg_cod, ($log->avg_cod === 'NaN' || $log->avg_cod === null) ? "0" : $log->avg_cod);
            array_push($stack_avg_flow_meter, ($log->avg_flow_meter === 'NaN' || $log->avg_flow_meter === null) ? "0" : $log->avg_flow_meter);
        }

        $result['global']['tstamp'] = $stackTstamp;
        $result['sensors']['ph'] = $stack_avg_ph;
        $result['sensors']['tss'] = $stack_avg_tss;
        $result['sensors']['amonia'] = $stack_avg_amonia;
        $result['sensors']['cod'] = $stack_avg_cod;
        $result['sensors']['flow_meter'] = $stack_avg_flow_meter;
        
        return json_encode($result);
    }

    
    private function sum( $a,  $b):int {
        return $a + $b;
    }
}

