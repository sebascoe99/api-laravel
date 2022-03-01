<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = Category::orderBy('create_date', 'desc')->get();
        return $categorias;
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
                'id_user' => 'required',
                'category_name' => 'required',
                'category_descripcion' => 'required',
                'category_thumbnail' => 'required',
                'category_status' => 'required',
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
            $nombreSinExtension = trim($request->category_thumbnail, $extensionImagen);
            $nombreFinal = $nombreSinExtension.$extensionImagen;
            $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $url="";
        }

        DB::enableQueryLog();
        $categoria =  new Category();
        $categoria->category_name = $request->category_name;
        $categoria->category_descripcion = $request->category_descripcion;
        $categoria->category_thumbnail = $url;
        $categoria->category_status = $request->category_status;
        $categoria->save();

        if(isset($categoria->id_category)){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = 'Se agregó nueva categoria'.' con nombre ' . $categoria->category_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_CATEGORY'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Categoria creada con exito',
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
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required',
                'category_name' => 'required',
                'category_descripcion' => 'required',
                'category_thumbnail' => 'required',
                'category_status' => 'required',
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
        $categoria = Category::findOrFail($request->id);//Se obtiene el objeto producto por el id

        if($request->hasFile('image')){//Comprobar si existe la imagen y no tenga valor null
            if(isset($categoria->category_thumbnail) && !is_null($categoria->category_thumbnail)){
                $imagenEliminar = str_replace('storage', 'public', $categoria->category_thumbnail);//reemplazar la palabra storage por public
                Storage::delete($imagenEliminar); //Eliminar la imagen actual de la categoria
            }

            $extensionImagen = '.'.$request->file('image')->extension();
            $nombreSinExtension = trim($request->category_thumbnail, $extensionImagen);
            $nombreFinal = $nombreSinExtension.$extensionImagen;
            $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            if(isset($categoria->category_thumbnail) && !is_null($categoria->category_thumbnail)){
                $imagenEliminar = str_replace('storage', 'public', $categoria->category_thumbnail);//reemplazar la palabra storage por public
                Storage::delete($imagenEliminar); //Eliminar la imagen actual de la categoria
            }
            $url="";
        }

        $categoria->category_name = $request->category_name;
        $categoria->category_descripcion = $request->category_descripcion;
        $categoria->category_thumbnail = $url;
        $categoria->category_status = $request->category_status;

        if($categoria->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizo la categoria'.' con nombre ' . $categoria->category_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_CATEGORY'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Categoria actualizada con exito',
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
        $categoria = Category::findOrFail($request->id);
        $categoria->category_status = $_ENV['STATUS_OFF'];
        if($categoria->save()){
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
