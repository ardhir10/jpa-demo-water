<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class SettingController extends Controller
{

    public function __invoke()
    {
        $data['page_title'] = 'Settings';
        return view('settings.index', $data);
    }

}
