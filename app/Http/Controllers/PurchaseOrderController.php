<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\InventaryI;
use App\Models\Producto;
use App\Models\Provider;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProducts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;
use \stdClass;

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
                'products' => 'required',
                'products.*.id_product' => 'required',
                'products.*.amount' => 'required',
                'products.*.product_name' => 'required'
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
        $purchase_order->purchase_order_status = $_ENV['STATUS_OFF'];
        $purchase_order->save();

        if(isset($purchase_order->id_purchase_order)){

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = $user->user_name.' '.$user->user_lastName.' '.' agregó nueva orden de compra.';
            $audit->audit_module = 'ORDEN_COMPRA';
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

            $proveedor = Provider::where('id_provider', $request->id_provider)->first();
            $mainController = new MailController();
            $mainController->sendEmailProvider($request->products, $proveedor->provider_email);

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

    public function confirmateProductsPurchaseOrder(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_purchase_order' => 'required|numeric|min:0|not_in:0',
                'id_user' => 'required|numeric|min:0|not_in:0',
                'purchase_order_total' => 'required',
                'tipe_of_pay' => 'required',
                'facture' => 'required',
                'date_purchase' => 'required',
                'products' => 'required',
                'products.*.id_purchase_order_products' => 'required',
                'products.*.id_product' => 'required',
                'products.*.purchase_order_products_status' => 'required',
                'products.*.purchase_order_products_amount' => 'required'
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

        $purchase_order = PurchaseOrder::where('id_purchase_order', $request->id_purchase_order)->first();

        foreach ($request->products as $pro) {
            $purchase_order_products = PurchaseOrderProducts::where('id_purchase_order_products', $pro['id_purchase_order_products'])->first();

            if($pro['purchase_order_products_status'] == 1){

                $purchase_order_products->purchase_order_products_amount = $pro['purchase_order_products_amount'];
                if($purchase_order_products->save()){
                    $producto = Producto::where('id_product', $pro['id_product'])->first();
                    $producto->product_stock = ($producto->product_stock + $pro['purchase_order_products_amount']);
                    $producto->save();

                    $inventario = new InventaryI();
                    $inventario->id_product =  $pro['id_product'];
                    $inventario->inventory_movement_type = $_ENV['INVENTORY_MOVEMENT_TYPE_INGRESO'];
                    $inventario->inventory_stock_amount = $pro['purchase_order_products_amount'];
                    $inventario->inventory_description = $_ENV['INVENTORY_DESCRIPTION_INGRESO_P'];
                    $inventario->save();
                }
            }else{
                $purchase_order_products->id_purchase_order_products_status == $_ENV['STATUS_OFF'];
                $purchase_order_products->save();
            }
        }
        $fecha = new DateTime($request->date_purchase);
        $fecha = $fecha->format('Y-m-d H:i:s');

        $purchase_order->purchase_order_status = $_ENV['STATUS_ON'];
        $purchase_order->purchase_order_total = $request->purchase_order_total;
        $purchase_order->tipe_of_pay = $request->tipe_of_pay;
        $purchase_order->facture = $request->facture;
        $purchase_order->date_purchase = $fecha;
        $user = User::where('id_user', $request->id_user)->first();

        DB::enableQueryLog();
        if($purchase_order->save()){

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['INVENTORY_MOVEMENT_TYPE_INGRESO'];
            $audit->audit_description = $user->user_name.' '.$user->user_lastName.' '.' confirmo la orden de compra con id '.$purchase_order->id_purchase_order;
            $audit->audit_module = 'ORDEN_COMPRA';
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Orden de compra completada con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

    public function getPurchasesByDate(Request $request){
        $cantidadDias = cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"));
        $anioMes = date('Y-m');
        $data = new stdClass();
        $count = new stdClass();

        if(isset($request->fechas)){

            if(count($request->fechas) == 1){
                foreach($request->fechas as $fecha){
                    $cantidadDias = cal_days_in_month(CAL_GREGORIAN, substr($fecha, -2), substr($fecha, -7, 4));

                    for ($i = 1; $i <= $cantidadDias; $i++) {
                        $dia = ($i < 10) ? '0'.$i : $i;
                        $compras = PurchaseOrder::orWhere('updated_at', 'like', $fecha . '-'. $dia . '%')
                        ->where('purchase_order_status', $_ENV['STATUS_ON'])->get();

                        $data->$i = count($compras);
                    }
                }

                $compras = PurchaseOrder::orWhere('updated_at', 'like', $fecha . '%')
                ->where('purchase_order_status', $_ENV['STATUS_ON'])->get();
                $count->Compras = count($compras);

                return response()->json([
                    'message' => 'Consulta realizada con exito',
                    'status' => $_ENV['CODE_STATUS_OK'],
                    'data' => $data,
                    'count' => $count
                ]);

            }else{

                foreach($request->fechas as $fecha){
                    $compras = PurchaseOrder::orWhere('updated_at', 'like', $fecha . '%')
                    ->where('purchase_order_status', $_ENV['STATUS_ON'])->get();

                    $orderClass = new OrderController;
                    $nombreMes = $orderClass->getNameMonth(substr($fecha, -2));
                    $data->$nombreMes = count($compras);
                }

                return response()->json([
                    'message' => 'Consulta realizada con exito',
                    'status' => $_ENV['CODE_STATUS_OK'],
                    'data' => $data
                ]);
            }
        }

        $count = new stdClass();

        for ($i = 1; $i <= $cantidadDias; $i++) {
            $dia = ($i < 10) ? '0'.$i : $i;
            $ventas = PurchaseOrder::orWhere('updated_at', 'like', $anioMes . '-'. $dia . '%')
            ->where('purchase_order_status', $_ENV['STATUS_ON'])->get();

            $data->$i = count($ventas);
        }

        $compras = PurchaseOrder::orWhere('updated_at', 'like', $anioMes . '%')
        ->where('purchase_order_status', $_ENV['STATUS_ON'])->get();
        $count->Compras = count($compras);

        return response()->json([
            'message' => 'Consulta realizada con exito',
            'status' => $_ENV['CODE_STATUS_OK'],
            'data' => $data,
            'count' => $count
        ]);
    }

}
