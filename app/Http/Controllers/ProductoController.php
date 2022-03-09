<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductosImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductoUnit;
use App\Models\Provider;
use Maatwebsite\Excel\HeadingRowImport;
use PhpOffice\PhpSpreadsheet\IOFactory;

use function PHPUnit\Framework\isNull;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $productos = Producto::with('brand', 'category')->orderBy('create_date', 'desc')->get();
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
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
                'id_provider' => 'required|numeric|min:0|not_in:0',
                'id_brand' => 'required|numeric|min:0|not_in:0',
                'id_category' => 'required|numeric|min:0|not_in:0',
                'id_product_unit' => 'required|numeric|min:0|not_in:0',
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
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
        }

        if($request->hasFile('image')){//Comprobar si existe la imagen y no tenga valor null
            $extensionImagen = '.'.$request->file('image')->extension();
            $nombreSinExtension = trim($request->product_image, $extensionImagen);
            $nombreFinal = $nombreSinExtension.'_'.$request->product_code.$extensionImagen;
            $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//Obtener la ruta temporal de la imagen y cambiar el nombre y almacenar en 'public/imagenes'
            $url = Storage::url($imagen);//Guardar la imagen en el Storage
        }else{
            $url="";
        }

        DB::enableQueryLog();
        $producto =  new Producto();
        $producto->id_user = intval($request->id_user);
        $producto->id_provider = intval($request->id_provider);
        $producto->id_brand = intval($request->id_brand);
        $producto->id_category = intval($request->id_category);
        $producto->id_product_unit = intval($request->id_product_unit);
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
            

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = 'Se agregó nuevo producto'.' con código ' . $producto->product_code;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PRODUCT'];
            $audit->audit_query = $queryStr;
            $audit->save();

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
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
                'id_provider' => 'required|numeric|min:0|not_in:0',
                'id_brand' => 'required|numeric|min:0|not_in:0',
                'id_category' => 'required|numeric|min:0|not_in:0',
                'id_product_unit' => 'required|numeric|min:0|not_in:0',
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
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
        }

        DB::enableQueryLog();
        $producto = Producto::findOrFail($request->id);//Se obtiene el objeto producto por el id
        if($request->hasFile('image')){//Comprobar si existe la imagen
            $url = $this->agregarImagen($request, $producto);
        }else{
            $url="";
            if(isset($request->product_image) && !(is_null($request->product_image))){//Cuando no se envia una nueva imagen y se mantiene la misma url en product_image
                $url = $request->product_image;//Se mantiene la misma url asociada a esa imagen
            }
            else{//Cuando se elimina por completo la url, es decir no quiere tener asociado una imagen a ese producto
                if(isset($producto->product_image) && !(is_null($producto->product_image))){//Preguntar si existia una imagen asociada al producto para borrarla del storage
                    $imagenEliminar = str_replace('storage', 'public', $producto->product_image);//reemplazar la palabra storage por public
                    Storage::delete($imagenEliminar); //Eliminar la imagen actual del producto
                }
            }
        }

        $producto->id_user = intval($request->id_user);
        $producto->id_provider = intval($request->id_provider);
        $producto->id_brand = intval($request->id_brand);
        $producto->id_category = intval($request->id_category);
        $producto->id_product_unit = intval($request->id_product_unit);
        $producto->product_name = $request->product_name;
        $producto->product_stock = intval($request->product_stock);
        $producto->product_code = $request->product_code;
        $producto->product_description = $request->product_description;
        $producto->product_price = $request->product_price;
        $producto->product_image = $url;
        $producto->product_status = intval($request->product_status);
        $producto->product_rating = intval($request->product_rating);

        if($producto->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizó el producto'.' con código ' . $producto->product_code;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PRODUCT'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Producto actualizado con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

    function agregarImagen(Request $request, Producto $producto)
    {
        if(isset($producto->product_image) && !is_null($producto->product_image)){
            $imagenEliminar = str_replace('storage', 'public', $producto->product_image);//reemplazar la palabra storage por public
            Storage::delete($imagenEliminar); //Eliminar la imagen actual del producto
        }

        $extensionImagen = '.'.$request->file('image')->extension();//Saber la extension de la imagen
        $nombreSinExtension = trim($request->product_image, $extensionImagen);//Nombre de la imagen sin extension
        $nombreFinal = $nombreSinExtension.'_'.$request->product_code.$extensionImagen;//nombre final ejemplo:"nombreproducto_codigo"
        $imagen = $request->file('image')->storeAs('public/imagenes', $nombreFinal);//almacenar la imagen en 'public/imagenes'
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
        $producto = Producto::findOrFail($request->id);
        $producto->product_status = $_ENV['STATUS_OFF'];
        if($producto->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ELIMINACION'];
            $audit->audit_description = 'Se eliminó el producto'.' con código ' . $producto->product_code;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PRODUCT'];
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function uploadExcel(Request $request)
    {
        set_time_limit(120);
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

        if(!($request->hasFile('excel'))){
            return response()->json([
                'message' => "No existe archivo",
                'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
            ]);
        }
        $the_file = $request->file('excel');
       try{
           $spreadsheet = IOFactory::load($the_file->getRealPath());
           $sheet        = $spreadsheet->getActiveSheet();
           $row_limit    = $sheet->getHighestDataRow();
           $column_limit = $sheet->getHighestDataColumn();
           $row_range    = range( 2, $row_limit );
           $column_range = range( 'F', $column_limit );
           $startcount = 2;
           $data = array();
           foreach ($row_range as $row ) {
               $data[] = [
                   'codigo' => intval($sheet->getCell( 'A' . $row )->getValue()),
                   'medida' => $sheet->getCell( 'B' . $row )->getValue(),
                   'articulo' => $sheet->getCell( 'C' . $row )->getValue(),
                   'precio1' => $sheet->getCell( 'D' . $row )->getValue(),
                   'tbodega' => $sheet->getCell( 'E' . $row )->getValue(),
               ];
               $startcount++;
           }

           //$arregloProductos = Producto::orderBy('create_date', 'desc')->get();//Obtener el arreglo de todos los productos
           $arregloUnidades = ProductoUnit::orderBy('create_date', 'desc')->get();//Obtener el arreglo de todos las Unidades

            foreach($data as $producto){

                if(!empty($producto['codigo']) && is_numeric($producto['codigo']) && $producto['codigo'] > 0){//Comprobar si el campo codigo del excel no este vacio, no sea numerico o sea negativo
                    if(isset($producto['medida']) && !(empty($producto['medida'])) && !(is_null($producto['medida']))){
                        if(isset($producto['articulo']) && !(empty($producto['articulo'])) && !(is_null($producto['articulo']))){
                            if(is_numeric($producto['precio1']) && $producto['precio1'] >= 0){
                                if(is_numeric($producto['tbodega']) && $producto['tbodega'] >=0){

                                    $productoPorCodigo = Producto::where('product_code', $producto['codigo'])->first();//Obtener el producto correspondiente a ese codigo registrado en la B

                                    if(isset($productoPorCodigo) && !(is_null($productoPorCodigo))){ //Comprobar si existe el codigo del registro de excel en la BD

                                        $id_product_unit_medida = ProductoUnit::where('description_product_unit', $producto['medida'])->pluck('id_product_unit')->first();

                                        if(isset($id_product_unit_medida) && !(is_null($id_product_unit_medida))){

                                            if(!($productoPorCodigo->product_code == $producto['codigo'] && $productoPorCodigo->id_product_unit ==  $id_product_unit_medida &&
                                               $productoPorCodigo->product_name == $producto['articulo'] && $productoPorCodigo->product_price == $producto['precio1'] &&
                                               $productoPorCodigo->product_stock == $producto['tbodega'])){

                                                $existeUnidad= false; $idUnidad = 1; $id_no_definido_unidad = 1;
                                                foreach ($arregloUnidades as $uni) {
                                                    if ($uni->description_product_unit == $producto['medida']) {
                                                        $existeUnidad = true;
                                                        $idUnidad = $uni->id_product_unit;
                                                        //break;
                                                    }

                                                    if ($uni->description_product_unit == $_ENV['NO_DEFINIDO']) {
                                                        $id_no_definido_unidad = $uni->id_product_unit;
                                                        //break;
                                                    }
                                                }

                                                if($existeUnidad){ //Comprobar si existe la unidad del registro de excel
                                                    if(!($productoPorCodigo->id_product_unit == $idUnidad)){//Comprobar si dicha unidad es diferente a la registrada en la BD
                                                        $productoPorCodigo->id_product_unit = intval($idUnidad);//Setear nuevo id de unidad en el producto registrado en la BD
                                                    }
                                                }
                                                else{
                                                    $productoPorCodigo->id_product_unit = intval($id_no_definido_unidad);// Setear dicho id de la unidad no definida en el producto registrado en BD
                                                }

                                                $productoPorNombre = Producto::where('product_name', $producto['articulo'])->first();//Obtener el producto correspondiente a ese nombre en BD

                                                if(isset($productoPorNombre) && !(is_null($productoPorNombre))){//Comprobar si existe el nombre del registro de excel si existe en BD
                                                    if(!($productoPorNombre->product_code == $producto['codigo'])){//Comprobar si dicho id del codigo es diferente a la registrada en la BD
                                                        $productoPorCodigo->product_name = $producto['articulo'];//Si es diferente setear nuevo nombre en la BD
                                                    }
                                                }
                                                else{
                                                    $productoPorCodigo->product_name = $producto['articulo'];//Setear nuevo nombre en la BD
                                                }

                                                if(!($productoPorCodigo->product_price == ($producto['precio1']))){//Comprobar si se mantiene el mismo precio
                                                    $productoPorCodigo->product_price = $producto['precio1'];//Setear nuevo precio
                                                }

                                                if(!($productoPorCodigo->product_stock == intval($producto['tbodega']))){//Comprobar si se mantiene el mismo stock
                                                    $productoPorCodigo->product_stock = $producto['tbodega'];//Setear nuevo stock
                                                }

                                                $productoPorCodigo->id_user = intval($request->id_user);
                                                if(!$productoPorCodigo->save()){
                                                    return response()->json([
                                                        'message' => 'Ocurrio un error interno en el servidor',
                                                        'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                    else{
                                        $productoNuevo =  new Producto();

                                        $productoPorNombre = Producto::where('product_name', $producto['articulo'])->first();//Obtener el producto correspondiente a ese nombre en BD

                                        $existeUnidad= false; $idUnidad = 1; $id_no_definido_unidad = 1;
                                        foreach ($arregloUnidades as $uni) {
                                            if ($uni->description_product_unit == $producto['medida']) {
                                                $existeUnidad = true;
                                                $idUnidad = $uni->id_product_unit;
                                            }

                                            if ($uni->description_product_unit == $_ENV['NO_DEFINIDO']) {
                                                $id_no_definido_unidad = $uni->id_product_unit;
                                            }
                                        }

                                        if($existeUnidad){ //Comprobar si existe la unidad del registro de excel en la BD
                                            $productoNuevo->id_product_unit = intval($idUnidad);//Setear nuevo id de unidad en el producto nuevo en la BD
                                        }
                                        else{
                                            $productoNuevo->id_product_unit = intval($id_no_definido_unidad);// Setear dicho id de la unidad no definida en el producto registrado en BD
                                        }

                                        if(isset($productoPorNombre) && !(is_null($productoPorNombre))){//Comprobar si existe el nombre del registro de excel si existe en BD
                                            $productoNuevo->product_name = $producto['articulo'] . ' 2';//Si es diferente setear nuevo nombre en la BD agregando el numero 2 para que no este repetido en la BD
                                        }
                                        else{
                                            $productoNuevo->product_name = $producto['articulo'];//Setear nuevo nombre en la BD
                                        }

                                        $productoNuevo->product_price = $producto['precio1'];//Setear nuevo precio
                                        $productoNuevo->product_stock = $producto['tbodega'];//Setear nuevo stock
                                        $productoNuevo->product_code = $producto['codigo'];//setear codigo al nuevo producto
                                        $productoNuevo->id_user = intval($request->id_user);//setear id_user al nuevo producto

                                        $id_no_definido_categoria = Category::where('category_descripcion', $_ENV['NO_DEFINIDO'])->first();
                                        $id_no_definido_categoria = isset($id_no_definido_categoria) ? $id_no_definido_categoria : 1;
                                        $productoNuevo->id_category = intval($id_no_definido_categoria);

                                        $id_no_definido_prove = Provider::where('provider_name', $_ENV['NO_DEFINIDO'])->first();
                                        $id_no_definido_prove = isset($id_no_definido_prove) ? $id_no_definido_prove : 1;
                                        $productoNuevo->id_provider = intval($id_no_definido_prove);

                                        $id_no_definido_marca = Brand::where('brand_name', $_ENV['NO_DEFINIDO'])->first();
                                        $id_no_definido_marca = isset($id_no_definido_marca) ? $id_no_definido_marca : 1;
                                        $productoNuevo->id_brand = intval($id_no_definido_marca);

                                        $productoNuevo->product_status = $_ENV['STATUS_ON'];

                                        if(!($productoNuevo->save())){
                                            return response()->json([
                                                'message' => 'Ocurrio un error interno en el servidor',
                                                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                                            ]);
                                        }
                                    }
                                }else{
                                    return response()->json([
                                        'message' => 'Error al intentar actualizar el stock del producto con codigo '.$producto['codigo'] . ' el precio no es un valor numerico o es negativo',
                                        'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
                                    ]);
                                }
                            }else{
                                return response()->json([
                                    'message' => 'Error al intentar actualizar el precio del producto con codigo '.$producto['codigo'] . ' el precio no es un valor numerico, es negativo o es igual a 0',
                                    'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
                                ]);
                            }
                        }else{
                            return response()->json([
                                'message' => 'Existe un articulo vacio con codigo '.$producto['codigo'],
                                'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
                            ]);
                        }
                    }else{
                        return response()->json([
                            'message' => 'Existe una medida vacia con codigo '.$producto['codigo'],
                            'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
                        ]);
                    }
                }else{
                    return response()->json([
                        'message' => 'Existe un codigo vacio, no numerico o es negativo',
                        'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
                    ]);
                }
            }

            return response()->json([
            'message' => 'Excel subido con exito',
            'status' => $_ENV['CODE_STATUS_OK']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

}
