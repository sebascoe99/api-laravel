<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $marcas = Brand::all()->where("brand_status","=",$_ENV['STATUS_ON']);
        return $marcas;
    }
}
