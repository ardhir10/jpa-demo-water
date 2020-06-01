<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JsonController extends Controller
{
    //

    public function __construct()
    {

    }

    // API
    public function trendingJson(Request $request)
    {
        $daterange = $request->daterange;
        $datewhere = $request->date;

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

        // $daterange = 'second';

        $logs = DB::
            table(DB::raw(" (
            SELECT *,
            ROW_NUMBER() OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY datetime asc) AS rn,

            avg(current_r) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as current_ravg,
            avg(current_s) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as current_savg,
            avg(current_t) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as current_tavg,
            avg(current_n) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as current_navg,

             avg(voltage_rn) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_rnavg,
            avg(voltage_sn) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_snavg,
            avg(voltage_tn) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_tnavg,
            avg(voltage_ln_rvg) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_lnavg,

            avg(voltage_rs) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_rsavg,
            avg(voltage_st) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_stavg,
            avg(voltage_tr) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_travg,
            avg(voltage_ll_rvg) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as voltage_llavg,


            avg(active_power_r) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as active_power_ravg,
            avg(active_power_s) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as active_power_savg,
            avg(active_power_t) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as active_power_tavg,
            avg(active_power_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as active_power_totalavg,

            avg(reactive_power_r) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as reactive_power_ravg,
            avg(reactive_power_s) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as reactive_power_savg,
            avg(reactive_power_t) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as reactive_power_tavg,
            avg(reactive_power_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as reactive_power_totalavg,

            avg(apparent_power_r) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as apparent_power_ravg,
            avg(apparent_power_s) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as apparent_power_savg,
            avg(apparent_power_t) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as apparent_power_tavg,
            avg(apparent_power_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id) as apparent_power_totalavg



 		 	           FROM logs
            ) as dm
            "))

        // FIRST_VALUE(voltage_ll_rvg) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id   asc) as firstValue,
        // last_value(voltage_ll_rvg) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  asc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as lastValue

            ->select(DB::raw("id,device_id,date_trunc('" . $daterange . "',datetime) as datetime,rn,
            current_ravg,current_savg,current_tavg,current_navg,
            voltage_rnavg,voltage_snavg,voltage_tnavg,voltage_tnavg,voltage_lnavg,
            voltage_rsavg,voltage_stavg,voltage_travg,voltage_travg,voltage_llavg,
            active_power_ravg,active_power_savg,active_power_tavg,active_power_totalavg,
            reactive_power_ravg,reactive_power_savg,reactive_power_tavg,reactive_power_totalavg,
            apparent_power_ravg,apparent_power_savg,apparent_power_tavg,apparent_power_totalavg
            "))
            ->where('rn', 1)
            ->where('device_id', 'SCHNEIDER')
            ->where("datetime", "LIKE", '%' . $datewhere . '%')
            ->orderBy('datetime', 'asc')
            ->get();

        // PECAH
        // --current
        $currentTstamp = [];
        $current_ravg = [];
        $current_savg = [];
        $current_tavg = [];
        $current_navg = [];

        // --voltage pp
        $voltage_rnavg = [];
        $voltage_snavg = [];
        $voltage_tnavg = [];
        $voltage_lnavg = [];

        // --voltage pp
        $voltage_rsavg = [];
        $voltage_stavg = [];
        $voltage_travg = [];
        $voltage_llavg = [];

        // --active power
        $active_power_ravg = [];
        $active_power_savg = [];
        $active_power_tavg = [];
        $active_power_totalavg = [];

        // --reactive power
        $reactive_power_ravg = [];
        $reactive_power_savg = [];
        $reactive_power_tavg = [];
        $reactive_power_totalavg = [];

        // --apparent power
        $apparent_power_ravg = [];
        $apparent_power_savg = [];
        $apparent_power_tavg = [];
        $apparent_power_totalavg = [];

        foreach ($logs as $log) {

            if ($daterange == 'year') {
                array_push($currentTstamp, date('Y', strtotime($log->datetime)));
            } elseif ($daterange == 'month') {
                array_push($currentTstamp, date('Y-m-d', strtotime($log->datetime)));
            } elseif ($daterange == 'day') {
                array_push($currentTstamp, date('Y-m-d', strtotime($log->datetime)));
            } else {
                array_push($currentTstamp, date('Y-m-d H:i:s', strtotime($log->datetime)));
            }
            array_push($current_ravg, $log->current_ravg);
            array_push($current_savg, $log->current_savg);
            array_push($current_tavg, $log->current_tavg);
            array_push($current_navg, $log->current_navg);

            array_push($voltage_rsavg, $log->voltage_rsavg);
            array_push($voltage_stavg, $log->voltage_stavg);
            array_push($voltage_travg, $log->voltage_travg);
            array_push($voltage_llavg, $log->voltage_llavg);

            array_push($voltage_rnavg, $log->voltage_rnavg);
            array_push($voltage_snavg, $log->voltage_snavg);
            array_push($voltage_tnavg, $log->voltage_tnavg);
            array_push($voltage_lnavg, $log->voltage_lnavg);

            array_push($active_power_ravg, $log->active_power_ravg);
            array_push($active_power_savg, $log->active_power_savg);
            array_push($active_power_tavg, $log->active_power_tavg);
            array_push($active_power_totalavg, $log->active_power_totalavg);

            array_push($reactive_power_ravg, $log->reactive_power_ravg);
            array_push($reactive_power_savg, $log->reactive_power_savg);
            array_push($reactive_power_tavg, $log->reactive_power_tavg);
            array_push($reactive_power_totalavg, $log->reactive_power_totalavg);

            array_push($apparent_power_ravg, $log->apparent_power_ravg);
            array_push($apparent_power_savg, $log->apparent_power_savg);
            array_push($apparent_power_tavg, $log->apparent_power_tavg);
            array_push($apparent_power_totalavg, $log->apparent_power_totalavg);

        }
        $result['current']['tstamp'] = $currentTstamp;
        $result['current']['current_ravg'] = $current_ravg;
        $result['current']['current_savg'] = $current_savg;
        $result['current']['current_tavg'] = $current_tavg;
        $result['current']['current_navg'] = $current_navg;

        $result['voltagepp']['tstamp'] = $currentTstamp;
        $result['voltagepp']['voltage_rsavg'] = $voltage_rsavg;
        $result['voltagepp']['voltage_stavg'] = $voltage_stavg;
        $result['voltagepp']['voltage_travg'] = $voltage_travg;
        $result['voltagepp']['voltage_llavg'] = $voltage_llavg;

        $result['voltagepn']['tstamp'] = $currentTstamp;
        $result['voltagepn']['voltage_rnavg'] = $voltage_rnavg;
        $result['voltagepn']['voltage_snavg'] = $voltage_snavg;
        $result['voltagepn']['voltage_tnavg'] = $voltage_tnavg;
        $result['voltagepn']['voltage_lnavg'] = $voltage_lnavg;

        $result['activepower']['tstamp'] = $currentTstamp;
        $result['activepower']['active_power_ravg'] = $active_power_ravg;
        $result['activepower']['active_power_savg'] = $active_power_savg;
        $result['activepower']['active_power_tavg'] = $active_power_tavg;
        $result['activepower']['active_power_totalavg'] = $active_power_totalavg;

        $result['reactivepower']['tstamp'] = $currentTstamp;
        $result['reactivepower']['reactive_power_ravg'] = $reactive_power_ravg;
        $result['reactivepower']['reactive_power_savg'] = $reactive_power_savg;
        $result['reactivepower']['reactive_power_tavg'] = $reactive_power_tavg;
        $result['reactivepower']['reactive_power_totalavg'] = $reactive_power_totalavg;

        $result['apparentpower']['tstamp'] = $currentTstamp;
        $result['apparentpower']['apparent_power_ravg'] = $apparent_power_ravg;
        $result['apparentpower']['apparent_power_savg'] = $apparent_power_savg;
        $result['apparentpower']['apparent_power_tavg'] = $apparent_power_tavg;
        $result['apparentpower']['apparent_power_totalavg'] = $apparent_power_totalavg;

        $result['date'] = $datewhere;
        // $result['trending']['current']['tstamp'] = $request->date;
        return $result;
    }

    public function consumptionJson(Request $request)
    {
        $daterange = $request->daterange;
        $datewhere = $request->date;

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

        // $daterange = 'second';

        $logs = DB::
            table(DB::raw(" (
            SELECT *,
            ROW_NUMBER() OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY datetime asc) AS rn,
            LAST_VALUE(energy_kwh_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kwh_exist,
            LAST_VALUE(energy_kvarh_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kvarh_exist,
            LAST_VALUE(energy_kvah_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kvah_exist,

            MAX(energy_kwh_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kwh_max,
            MIN(energy_kwh_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kwh_min,

            MAX(energy_kvarh_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kvarh_max,
            MIN(energy_kvarh_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kvarh_min,

            MAX(energy_kvah_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kvah_max,
            MIN(energy_kvah_total) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  desc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as kvah_min

 		 	           FROM logs
            ) as dm
            "))

        // FIRST_VALUE(voltage_ll_rvg) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id   asc) as firstValue,
        // last_value(voltage_ll_rvg) OVER (PARTITION BY date_trunc('" . $daterange . "',datetime),device_id ORDER BY id  asc ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) as lastValue

            ->select(DB::raw("id,device_id,date_trunc('" . $daterange . "',datetime) as datetime,rn,
            kwh_exist,
            kvarh_exist,
            kvah_exist,

            kwh_max,
            kwh_min,
            (kwh_max - kwh_min) as kwh_total,

            kvarh_max,
            kvarh_min,
            (kvarh_max - kvarh_min) as kvarh_total,

            kvah_max,
            kvah_min,
            (kvah_max - kvah_min) as kvah_total
            "))
            ->where('rn', 1)
            ->where('device_id', 'SCHNEIDER')
            ->where("datetime", "LIKE", '%' . $datewhere . '%')
            ->orderBy('datetime', 'asc')
            ->get();

        // --current
        $consumptionTstamp = [];
        $kwh_exist = [];
        $kvarh_exist = [];
        $kvah_exist = [];

        $kwh_total = [];
        $kvarh_total = [];
        $kvah_total = [];

        foreach ($logs as $log) {
            if ($daterange == 'year') {
                array_push($consumptionTstamp, date('Y', strtotime($log->datetime)));
            } elseif ($daterange == 'month') {
                array_push($consumptionTstamp, date('Y-m-d', strtotime($log->datetime)));
            } elseif ($daterange == 'day') {
                array_push($consumptionTstamp, date('Y-m-d', strtotime($log->datetime)));
            } else {
                array_push($consumptionTstamp, date('Y-m-d H:i:s', strtotime($log->datetime)));
            }
            array_push($kwh_exist, $log->kwh_exist);
            array_push($kvarh_exist, $log->kvarh_exist);
            array_push($kvah_exist, $log->kvah_exist);

            array_push($kwh_total, $log->kwh_total);
            array_push($kvarh_total, $log->kvarh_total);
            array_push($kvah_total, $log->kvah_total);
        }

        $result['dataexist']['all'] = $logs;
        $result['dataexist']['tstamp'] = $consumptionTstamp;
        $result['dataexist']['kwh_exist'] = $kwh_exist;
        $result['dataexist']['kvarh_exist'] = $kvarh_exist;
        $result['dataexist']['kvah_exist'] = $kvah_exist;

        $result['datatotal']['tstamp'] = $consumptionTstamp;
        $result['datatotal']['kwh_total'] = $kwh_total;
        $result['datatotal']['kvarh_total'] = $kvarh_total;
        $result['datatotal']['kvah_total'] = $kvah_total;

        $result['date'] = $datewhere;
        return $result;

    }
}
