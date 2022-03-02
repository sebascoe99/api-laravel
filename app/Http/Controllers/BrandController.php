<?php

namespace App\Http\Controllers;

use App\Models\Audit;
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
                'id_user' => 'required',
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

        DB::enableQueryLog();
        $marca =  new Brand();
        $marca->brand_name = $request->brand_name;
        $marca->brand_thumbnail = $url;
        $marca->brand_status = $request->brand_status;
        $marca->save();

        if(isset($marca->id_brand)){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = 'Se agregó nueva marca'.' con nombre ' . $marca->brand_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_BRAND'];
            $audit->audit_query = $queryStr;
            $audit->save();

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
                'id_user' => 'required',
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

        DB::enableQueryLog();
        $marca = Brand::findOrFail($request->id);//Se obtiene el objeto marca por el id

        if($request->hasFile('image')){//Comprobar si existe la imagen
            $url = $this->agregarImagen($request, $marca);
        }else{
            $url="";
            if(isset($request->brand_thumbnail) && !(is_null($request->brand_thumbnail))){
                $url = $request->brand_thumbnail;
            }else{
                if(isset($marca->brand_thumbnail) && !(is_null($marca->brand_thumbnail))){
                    $imagenEliminar = str_replace('storage', 'public', $marca->brand_thumbnail);//reemplazar la palabra storage por public
                    Storage::delete($imagenEliminar); //Eliminar la imagen actual de la marca
                }
            }
        }

        $marca->brand_name = $request->brand_name;
        $marca->brand_thumbnail = $url;
        $marca->brand_status = $request->brand_status;

        if($marca->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizo la marca'.' con nombre ' . $marca->brand_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_BRAND'];
            $audit->audit_query = $queryStr;
            $audit->save();

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

    function agregarImagen(Request $request, Brand $marca)
    {
        if(isset($marca->brand_thumbnail) && !is_null($marca->brand_thumbnail)){
            $imagenEliminar = str_replace('storage', 'public', $marca->brand_thumbnail);//reemplazar la palabra storage por public
            Storage::delete($imagenEliminar); //Eliminar la imagen actual de la marca
        }

        $extensionImagen = '.'.$request->file('image')->extension();
        $nombreSinExtension = trim($request->brand_thumbnail, $extensionImagen);
        $nombreFinal = $nombreSinExtension.$extensionImagen;
        $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
        $url = Storage::url($imagen);//Guardar la imagen en el Storage
        return $url;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
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

        DB::enableQueryLog();
        $marca = Brand::findOrFail($request->id);
        $marca->brand_status = $_ENV['STATUS_OFF'];
        if($marca->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ELIMINACION'];
            $audit->audit_description = 'Se elimino la marca'.' con nombre ' . $marca->brand_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_BRAND'];
            $audit->audit_query = $queryStr;
            $audit->save();

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
