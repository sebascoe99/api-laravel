<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
         //$productos = Producto::all()->where("product_status","=",$_ENV['PRODUCT_STATUS_ON']);
         $productos = Producto::orderBy('create_date', 'desc')->get()->where("product_status","=",$_ENV['STATUS_ON']);
         return $productos;
        //$productos = Producto::with('productoCategorias')->get()
        //->where("product_status","=",$_ENV['PRODUCT_STATUS_ON']);
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

        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required',
                'id_provider' => 'required',
                'id_brand' => 'required',
                'id_category' => 'required',
                'product_name' => 'required',
                'product_stock' => 'required',
                'product_code' => 'required',
                'product_description' => 'required',
                'product_price' => 'required',
                'product_status' => 'required',
                'product_rating' => 'required'
            ],
            [
                'required' => 'El campo :attribute es requerido'
            ]);

            if($validator->fails()){

                return response()->json([
                    'message' => $validator->errors(),
                    'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
                ]);
            }

        }catch (\Exception $e){
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
                ]);
        }

        if(isset($request->image) && !is_null($request->image)){//Comprobar si existe la imagen y no tenga valor null
            $imagen = ($request->image)->store('public/imagenes');//Obtener la ruta temporal de la imagen y cambiar a 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $url="";
        }

        $producto->id_user = intval($request->id_user);
        $producto->id_provider = intval($request->id_provider);
        $producto->id_brand = intval($request->id_brand);
        $producto->id_category = intval($request->id_category);
        $producto->product_name = $request->product_name;
        $producto->product_stock = intval($request->product_stock);
        $producto->product_code = $request->product_code;
        $producto->product_description = $request->product_description;
        $producto->product_price = $request->product_price;
        $producto->product_image = $url;
        $producto->product_status = intval($request->product_status);
        $producto->product_rating = intval($request->product_rating);
        $producto->save();

        if(isset($producto->id_product)){
            return response()->json([
                'message' => 'Producto creado con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
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
        $producto =  new Producto();
        $producto = Producto::findOrFail($request->id);//Se obtiene el objeto producto por el id

        if(isset($producto->product_image) && isset($request->image) && strcmp($request->image, $producto->product_image) == 0){//Comparar si la url de la imagen es igual a la que ya esta almacenada
            $urlNueva = $request->image;
        }
        else if(isset($producto->product_image)){//Se comprueba si existe una imagen relacionada al producto
            $urlVieja = str_replace('/storage', 'public', $producto->product_image);//Se quita "/storage" de la URL y se agrega "public"
            Storage::delete($urlVieja);// Se elimina imagen
        }


        if(isset($request->image) && !is_null($request->image) && !empty($request->image)){//Comprobar si existe la imagen y no tenga valor null
            $imagen = ($request->image)->store('public/imagenes');//Obtener la ruta temporal de la imagen y cambiar a 'public/imagenes'
            $urlNueva = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $urlNueva = "";
        }

        $producto->id_user = intval($request->id_user);
        $producto->id_provider = intval($request->id_provider);
        $producto->id_brand = intval($request->id_brand);
        $producto->product_name = $request->product_name;
        $producto->product_stock = intval($request->product_stock);
        $producto->product_code = $request->product_code;
        $producto->product_description = $request->product_description;
        $producto->product_price = $request->product_price;
        $producto->product_image = $urlNueva;
        $producto->product_status = intval($request->product_status);
        $producto->product_rating = intval($request->product_rating);

        $producto->save();

        return $producto->id_product;

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
        return response()->json([
            'message' => 'Eliminado correctamente',
            'status' => $_ENV['CODE_STATUS_OK']
        ]);
    }
}
