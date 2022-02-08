<?php

namespace App\Http\Controllers;

use App\Models\ProductoCategoria;
use Illuminate\Http\Request;

class ProductoCategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
        foreach ($request->id_category as $categoria) {
            $producto_categoria =  new ProductoCategoria();

            $producto_categoria->id_product = intval($request->id_product);
            $producto_categoria->id_category = intval($categoria);
            $producto_categoria->product_category_status = $_ENV['STATUS_ON'];

            $producto_categoria->save();
        }

        return response()->json([
            'message' => 'Creado con exito',
            'status' => 200
        ]);
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
        $producto_categoria =  new ProductoCategoria();

        $producto_categoria = ProductoCategoria::where('id_product', '=', $request->id)->get();
        //return $producto_categoria;
        if(isset($producto_categoria) && !empty($producto_categoria)){

            foreach ($producto_categoria as $categoriaExistente) {

                foreach ($request->categorias as $categoria){

                    if(!$categoriaExistente == $categoria){

                    }

                    $producto_categoria =  new ProductoCategoria();

                    $producto_categoria->id_product = intval($request->id_product);
                    $producto_categoria->id_category = intval($categoria);
                    $producto_categoria->product_category_status = $_ENV['PRODUCT_CATEGORY_STATUS_ON'];

                    $producto_categoria->save();
                }
            }
        }
        return 0;
        return response()->json([
            'message' => 'Creado con exito',
            'status' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
