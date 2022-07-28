<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Producto;
use App\Models\Promotion;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promociones = Promotion::with('producto')->orderBy('create_date', 'desc')->get();
        return $promociones;
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
                'id_product' => 'required|numeric|min:0|not_in:0',
                'promotion_discount' => 'required|numeric|min:0|not_in:0',
                'promotion_date_start' => 'required',
                'promotion_date_of_expiry' => 'required',
                'promotion_status' => 'required|numeric|min:0'
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
        $promocion =  new Promotion();
        $producto = Producto::where('id_product', $request->id_product)->first();

        if(isset($request->promotion_description))
            $promocion->promotion_description  = $request->promotion_description;

        $promocion->id_product = $producto->id_product;
        $promocion->promotion_discount = $request->promotion_discount;
        $promocion->promotion_date_start = $request->promotion_date_start;
        $promocion->promotion_date_of_expiry = $request->promotion_date_of_expiry;
        $promocion->promotion_status = $request->promotion_status;
        $promocion->save();

        if(isset($promocion->id_promotion)){

            $existeProducto = ShoppingCart::where('id_product', $request->id_product)->where('promotion_status', $_ENV['STATUS_OFF'])->get();
            if(count($existeProducto) >= 1){
                $precioProducto = round(Producto::where('id_product', $request->id_product)->pluck('product_price')->first(), 2);
                $precioConDescuento = round($precioProducto * $request->promotion_discount, 2);

                $actualizar = ShoppingCart::where('id_product', $request->id_product)
                ->update(['product_offered_price_total' => $precioConDescuento,
                          'product_offered' => $request->promotion_discount]);
            }

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = 'Se agregó nueva promoción'.' con el producto ' . $producto->product_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROMOTION'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Promocion creada con exito',
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
                    'id_product' => 'required|numeric|min:0|not_in:0',
                    'promotion_discount' => 'required|numeric|min:0|not_in:0',
                    'promotion_date_start' => 'required',
                    'promotion_date_of_expiry' => 'required',
                    'promotion_status' => 'required|numeric|min:0'
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
        $promocion = Promotion::where('id_promotion', $request->id)->first();
        $producto = Producto::where('id_product', $request->id_product)->first();

        if(isset($request->promotion_description))
            $promocion->promotion_description = $request->promotion_description;

        $promocion->id_product = $producto->id_product;
        $promocion->promotion_discount = $request->promotion_discount;
        $promocion->promotion_date_start  = $request->promotion_date_start;
        $promocion->promotion_date_of_expiry  = $request->promotion_date_of_expiry;
        $promocion->promotion_status  = $request->promotion_status;
        $promocion->save();

        if(isset($promocion->id_promotion)){

            $existeProducto = ShoppingCart::where('id_product', $request->id_product)->where('promotion_status', $_ENV['STATUS_OFF'])->get();
            if(count($existeProducto) >= 1){
                $precioProducto = round(Producto::where('id_product', $request->id_product)->pluck('product_price')->first(), 2);
                $precioConDescuento = round($precioProducto * $request->promotion_discount, 2);

                $actualizar = ShoppingCart::where('id_product', $request->id_product)
                ->update(['product_offered_price_total' => $precioConDescuento,
                          'product_offered' => $request->promotion_discount]);
            }

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizó la promoción'.' con el producto ' . $producto->product_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROMOTION'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Promoción actualizada con exito',
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
        $promocion = Promotion::findOrFail($request->id);
        $producto = Producto::where('id_product', $promocion->id_product)->first();

        $promocion->promotion_status = $_ENV['STATUS_OFF'];
        if($promocion->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ELIMINACION'];
            $audit->audit_description = 'Se eliminó la promoción'.' con el producto ' . $producto->product_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROMOTION'];
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
