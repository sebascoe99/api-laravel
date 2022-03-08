<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Producto;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promociones = Promotion::all()->where("id_promotion_status","=",$_ENV['STATUS_ON']);
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
                'product_code' => 'required|numeric|min:0|not_in:0',
                'discount' => 'required|numeric|min:0|not_in:0',
                'date_expiry' => 'required',
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
        $producto = Producto::where('product_code', $request->product_code)->first();

        $promocion->id_product = $producto->id_product;
        $promocion->discount = $request->discount;
        $promocion->date_expiry = $request->date_expiry;
        $promocion->id_promotion_status = $_ENV['STATUS_ON'];
        $promocion->save();

        if(isset($promocion->id_promotion)){
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
        //
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
