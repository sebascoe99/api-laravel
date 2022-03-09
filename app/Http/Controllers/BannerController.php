<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = Banner::all()->where("banner_status","=",$_ENV['STATUS_ON']);
        return $banners;
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
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
                'banner_name' => 'required',
                'banner_status' => 'required|numeric|min:0',
                'banner_thumbnail' => 'required'
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
            $nombreSinExtension = trim($request->banner_thumbnail, $extensionImagen);
            $nombreFinal = $nombreSinExtension.$extensionImagen;
            $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $url="";
        }

        DB::enableQueryLog();
        $banner =  new Banner();
        $banner->banner_name = $request->banner_name;

        if(isset($request->banner_description))
            $banner->banner_description = $request->banner_description;

        $banner->banner_image = $url;
        $banner->banner_status = $request->banner_status;
        $banner->save();

        if(isset($banner->id_banner)){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = 'Se agregó un nuevo banner'.' con nombre ' . $banner->banner_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_BANNER'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Banner creado con exito',
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
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
                'banner_name' => 'required',
                'banner_status' => 'required|numeric|min:0',
                'banner_thumbnail' => 'required'
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

        $banner = Banner::findOrFail($request->id);//Se obtiene el objeto banner por el id
        if($request->hasFile('image')){//Comprobar si existe la imagen
            $url = $this->agregarImagen($request, $banner);
        }else{
            $url="";
            if(isset($request->banner_thumbnail) && !(is_null($request->banner_thumbnail))){//Cuando no se envia una nueva imagen y se mantiene la misma url en banner_thumbnail
                $url = $request->banner_thumbnail;//Se mantiene la misma url asociada a esa imagen
            }
            else{//Cuando se elimina por completo la url, es decir no quiere tener asociado una imagen a ese banner
                if(isset($banner->banner_thumbnail) && !(is_null($banner->banner_thumbnail))){//Preguntar si existia una imagen asociada al banner para borrarla del storage
                    $imagenEliminar = str_replace('storage', 'public', $banner->banner_thumbnail);//reemplazar la palabra storage por public
                    Storage::delete($imagenEliminar); //Eliminar la imagen actual del banner
                }
            }
        }

        DB::enableQueryLog();
        $banner =  new Banner();
        $banner->banner_name = $request->banner_name;

        if(isset($request->banner_description))
            $banner->banner_description = $request->banner_description;

        $banner->banner_image = $url;
        $banner->banner_status = $request->banner_status;
        $banner->save();

        if(isset($banner->id_banner)){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizó el banner'.' con nombre ' . $banner->banner_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_BANNER'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Banner creado con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

    function agregarImagen(Request $request, Banner $banner)
    {
        if(isset($banner->banner_thumbnail) && !is_null($banner->banner_thumbnail)){
            $imagenEliminar = str_replace('storage', 'public', $banner->banner_thumbnail);//reemplazar la palabra storage por public
            Storage::delete($imagenEliminar); //Eliminar la imagen actual de la categoria
        }

        $extensionImagen = '.'.$request->file('image')->extension();
        $nombreSinExtension = trim($request->banner_thumbnail, $extensionImagen);
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
        $banner = Banner::findOrFail($request->id);

        $banner->banner_status = $_ENV['STATUS_OFF'];
        if($banner->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ELIMINACION'];
            $audit->audit_description = 'Se eliminó el banner'.' con el nombre ' . $banner->banner_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_BANNER'];
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
