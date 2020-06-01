<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

 
Route::get('/', 'MonitoringController');


// DASHBOARD
Route::get('/dashboard', 'DashboardController');

// TRENDING REPORT
Route::get('/trending/report', 'TrendingReportController@index');

// ALARM
Route::prefix('alarm')->group(function () {
    Route::get('/alarm-list', 'AlarmController@alarmList');
    Route::get('/alarm-setting', 'AlarmController@alarmSetting');    
});

// API
Route::get('/api/logs', 'ApiController@logs');

// SETTING
Route::prefix('settings')->group(function () {
    //== All Setting
    Route::get('/', 'SettingController');
    
    //== Device 
    Route::get('/device', 'DeviceController@index');
    Route::get('/device/{id}', 'DeviceController@detail');

    //== Sensor
    Route::get('/sensor', 'SensorController@index');


    //== SOCKET
    Route::get('/socket', 'GlobalSettingController@socket');
    Route::post('/socket/{id?}', 'GlobalSettingController@updateSocket');
    
    
    //== DB
    Route::get('/database', 'GlobalSettingController@database');
    Route::post('/database/backup', 'BackupController@backup');
    Route::post('/database/{id?}', 'GlobalSettingController@updateDatabase');
    
    
    //== API
    Route::get('/api-config', 'ApiSettingController@apiConfig');
    Route::post('/api-config/{id?}', 'ApiSettingController@updateApi');

});

// Route::post('/api/sensors', 'SensorController@active');



// REPORT 
// --SETTING
Route::prefix('report')->group(function () {
    Route::get('/weight-bridge', 'WeightBridgeController@index');
    
    
    Route::get('/intake', 'IntakeController@index');


    Route::get('/hammer-mill', 'HammerMillController@index');

    Route::get('/pellet-mill', 'PelletMillController@index');

    Route::get('/mixer', 'MixerController@index');

    Route::get('/pellet', 'ReportController@pellet');
    Route::get('/mixer/detail/{id}', 'ReportController@mixerDetail');
});

Auth::routes();
 


// --USER RESOURCE
Route::resource('/users', 'UserController');

// --DEPARTEMENT RESOURCE
Route::resource('/departements', 'DepartementController');


 
 
