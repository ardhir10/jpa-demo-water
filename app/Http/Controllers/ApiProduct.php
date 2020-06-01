<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiProduct extends Controller
{
    public function all(){
        $products = \App\Product::orderBy('id', 'desc')->get();
        $results = $products; 
        return $results;
    }
}
