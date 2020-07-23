<?php

namespace App\Http\Controllers;

use App\Departement as Departements;
use App\User as Users;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');

        // $this->middleware('privilege:Dashboard');
       
    }

    public function __invoke(Request $request)
    {

        $data['users'] = Users::paginate(5);
        $data['page_title'] = 'Dashboard';
        $data['departements'] = Departements::all();
        $data['sensors'] = \App\Sensor::orderBy('id','asc')->whereSensorStatus(1)->get();
        return view('dashboard.index', $data);
    }

    
    
}
