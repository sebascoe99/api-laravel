<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $marcas = Brand::orderBy('create_date', 'desc')->get();
        return $marcas;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'brand_name' => 'required',
                'brand_status' => 'required',
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
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
        }

        if($request->hasFile('image')){//Comprobar si existe la imagen y no tenga valor null
            $extensionImagen = '.'.$request->file('image')->extension();
            $nombreSinExtension = trim($request->brand_thumbnail, $extensionImagen);
            $nombreFinal = $nombreSinExtension.$extensionImagen;
            $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $url="";
        }

        //DB::enableQueryLog();
        $marca =  new Brand();
        $marca->brand_name = $request->brand_name;
        $marca->brand_thumbnail = $url;
        $marca->brand_status = $request->brand_status;
        $marca->save();

        if(isset($marca->id_brand)){
            return response()->json([
                'message' => 'Marca creada con exito',
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'brand_name' => 'required',
                'brand_status' => 'required',
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
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
        }

        $marca = Brand::findOrFail($request->id);//Se obtiene el objeto marca por el id

        if($request->hasFile('image')){//Comprobar si existe la imagen
            $imagenEliminar = str_replace('storage', 'public', $marca->brand_thumbnail);//reemplazar la palabra storage por public
            Storage::delete($imagenEliminar); //Eliminar la imagen actual de la marca

            $extensionImagen = '.'.$request->file('image')->extension();
            $nombreSinExtension = trim($request->brand_thumbnail, $extensionImagen);
            $nombreFinal = $nombreSinExtension.$extensionImagen;
            $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $url = $request->brand_thumbnail;
        }

        $marca->brand_name = $request->brand_name;
        $marca->brand_thumbnail = $url;
        $marca->brand_status = $request->brand_status;

        if($marca->save()){
            return response()->json([
                'message' => 'Marca actualizada con exito',
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $marca = Brand::findOrFail($request->id);
        $marca->brand_status = $_ENV['STATUS_OFF'];
        if($marca->save()){
            return response()->json([
                'message' => 'Eliminado correctamente',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }
}
