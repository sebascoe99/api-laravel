<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Producto::_db()->debug = true;
        $productos = Producto::all()->where("product_status","=",$_ENV['PRODUCT_STATUS_ON']);
        return $productos;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $producto =  new Producto();

        $imagen = ($request->image)->store('public/imagenes');
        $url = Storage::url($imagen);

        $producto->id_user = intval($request->id_user);
        $producto->id_provider = intval($request->id_provider);
        $producto->id_brand = intval($request->id_brand);
        $producto->product_stock = intval($request->product_stock);
        $producto->product_code = $request->product_code;
        $producto->product_description = $request->product_description;
        $producto->product_price = $request->product_price;
        $producto->product_image = $url;
        $producto->product_status = intval($request->product_status);
        $producto->product_rating = intval($request->product_rating);

        $producto->save();

        return $producto;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //$flight = Producto::where('id_product', $request->id)->first();
        //return  $flight;
        $producto = Producto::findOrFail($request->id);

        $producto->id_user = $request->id_user;
        $producto->id_provider = $request->id_provider;
        $producto->id_brand = $request->id_brand;
        $producto->product_stock = $request->product_stock;
        $producto->product_code = $request->product_code;
        $producto->product_description = $request->product_description;
        $producto->product_stock_minimum = $request->product_stock_minimum;
        $producto->product_price = $request->product_price;
        $producto->product_image = $request->product_image;
        $producto->product_status = $request->product_status;
        $producto->product_rating = $request->product_rating;

        $producto->save();

        return $producto;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $producto = Producto::findOrFail($request->id);

        $producto->product_status = 0;

        $producto->save();

        return $producto;
    }
}
