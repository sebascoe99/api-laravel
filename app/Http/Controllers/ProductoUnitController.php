<?php

namespace App\Http\Controllers;

use App\Models\ProductoUnit;
use Illuminate\Http\Request;

class ProductoUnitController extends Controller
{
    public function index()
    {
        $unidades = ProductoUnit::all()->where("product_unit_status","=",$_ENV['STATUS_ON']);
        return $unidades;

    }
}
