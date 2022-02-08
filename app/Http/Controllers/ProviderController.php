<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;


class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $proveedores = Provider::all()->where("provider_status","=",$_ENV['STATUS_ON']);
        return $proveedores;
    }
}
