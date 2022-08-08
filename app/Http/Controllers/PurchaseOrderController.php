<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProducts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ordenes_compra = PurchaseOrder::with('provider', 'user', 'PurchaseOrderProductos', 'PurchaseOrderProductos.producto')
        ->orderBy('create_date', 'desc')->get();
        return $ordenes_compra;
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
                'id_provider' => 'required|numeric|min:0|not_in:0',
                'id_user' => 'required|numeric|min:0|not_in:0',
                //'purchase_order_total' => 'required',
                'products' => 'required',
                'products.*.id_product' => 'required',
                'products.*.amount' => 'required',
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
        $purchase_order = new PurchaseOrder();
        $user = User::where('id_user', $request->id_user)->first();

        $purchase_order->id_provider = $request->id_provider;
        $purchase_order->id_user = $request->id_user;
        $purchase_order->purchase_order_status = $_ENV['STATUS_ON'];
        //$purchase_order->purchase_order_total = $request->purchase_order_total;
        $purchase_order->save();

        if(isset($purchase_order->id_purchase_order)){

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = $user->user_name.' '.$user->user_lastName.' '.' agregó nueva orden de compra.';
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROMOTION'];
            $audit->audit_query = $queryStr;
            $audit->save();

            foreach ($request->products as $producto) {
                $purchase_order_products = new PurchaseOrderProducts();
                $purchase_order_products->id_product = $producto['id_product'];
                $purchase_order_products->id_purchase_order = $purchase_order->id_purchase_order;
                $purchase_order_products->purchase_order_products_status = $_ENV['STATUS_ON'];
                $purchase_order_products->purchase_order_products_amount = $producto['amount'];
                $purchase_order_products->save();
            }

            return response()->json([
                'message' => 'Orden de compra creada con exito',
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
