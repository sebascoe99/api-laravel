<?php

namespace App\Http\Controllers;

use App\Models\InventaryE;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderOrderDetail;
use App\Models\OrderStatus;
use App\Models\Producto;
use App\Models\ShoppingCart;
use App\Models\TypePay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;

class OrderController extends Controller
{
    private static $id_user_global = null;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                'order_price_total' => 'required|numeric|min:0|not_in:0',
                'id_address' => 'required|numeric|min:0|not_in:0',
                'address_reference' => 'required',
                'type_of_pay' => 'required|numeric|min:0|not_in:0'
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

        $id_order_status_pending = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_PENDING'])->pluck('id_order_status')->first();
        if(!isset($id_order_status_pending)){
            return response()->json([
                'message' => 'Ocurrio un error interno al agregar el status de la orden',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }

        $voucher_number_final = Order::max('id_order');
        if(isset($voucher_number_final) && !is_null($voucher_number_final)){
            $serie = '001';
            $vaucher_present = $voucher_number_final + 1;
            $vaucher_present = str_pad($vaucher_present, 9, '0', STR_PAD_LEFT);
            $vaucher_present = $serie.'-'. $serie.'-'.$vaucher_present;
        }else{
            $serie = '001';
            $vaucher_present = 1;
            $vaucher_present = str_pad($vaucher_present, 9, '0', STR_PAD_LEFT);
            $vaucher_present = $serie.'-'. $serie.'-'.$vaucher_present;
        }

        if(!($request->type_of_pay == 1 || $request->type_of_pay == 2))
            $request->type_of_pay = 1;

        $orden = new Order();
        $orden->id_user = $request->id_user;
        $orden->id_order_status = $id_order_status_pending;
        $orden->id_pay = $request->type_of_pay;
        $orden->order_price_total = $request->order_price_total;
        $orden->voucher_number = $vaucher_present;

        if($orden->save()){
            $id_order = $orden->id_order;

            if(!isset($orden->id_order)){
                $orden = Order::find($id_order);
                $orden->delete();

                return response()->json([
                    'message' => 'Ocurrio un error interno al agregar el tipo de pago',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $products = (array) $request->products;
            foreach($products as $product){
                $orden_detalle = new OrderDetail();
                $orden_detalle->id_product = intval($product['id_product']);
                $orden_detalle->order_detail_quantity = intval($product['product_amount_sail']);
                if(array_key_exists('product_offered', $product)){
                    $orden_detalle->order_detail_discount = $product['product_offered'];
                }else{
                    $orden_detalle->order_detail_discount = 0;
                }
                $orden_detalle->order_detail_subtotal = ($product['product_price'] * intval($product['product_amount_sail']));
                $orden_detalle->order_detail_iva = 12;
                $orden_detalle->order_detail_total = ($product['product_price'] * intval($product['product_amount_sail']));
                $orden_detalle->id_address = $request->id_address;
                $orden_detalle->address_reference = $request->address_reference;

                if(!$orden_detalle->save()){
                    $orden = Order::find($id_order);
                    $orden->delete();

                    return response()->json([
                        'message' => 'Ocurrio un error interno al crear la orden detalle',
                        'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                    ]);
                }

                $orden_orden_detalle = new OrderOrderDetail();
                $orden_orden_detalle->id_order = $id_order;
                $orden_orden_detalle->id_order_detail = $orden_detalle->id_order_detail;

                if(!$orden_orden_detalle->save()){
                    $orden = Order::find($id_order);
                    $orden->delete();

                    $ordenDetalle = OrderDetail::find($orden_detalle->id_order_detail);
                    $ordenDetalle->delete();

                    return response()->json([
                        'message' => 'Ocurrio un error interno al crear la orden detalle maestro',
                        'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                    ]);
                }

                $description = "VENTA ONLINE";
                $inventario_e = new InventaryE();
                $inventario_e->id_order = $id_order;
                $inventario_e->id_order_detail = $orden_detalle->id_order_detail;
                $inventario_e->inventory_movement_type = $_ENV['INVENTORY_MOVEMENT_TYPE_EGRESO'];
                $inventario_e->inventory_stock_amount = $product['product_amount_sail'];
                $inventario_e->inventory_description = $description;

                if(!$inventario_e->save()){
                    $orden = Order::find($id_order);
                    $orden->delete();

                    $ordenDetalle = OrderDetail::find($orden_detalle->id_order_detail);
                    $ordenDetalle->delete();

                    $orden_orden_detalle = OrderOrderDetail::find($orden_orden_detalle->id_order_order_detail);
                    $orden_orden_detalle->delete();


                    return response()->json([
                        'message' => 'Ocurrio un error interno al crear la orden dentro del inventario de tipo egreso',
                        'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                    ]);
                }

                $producto = Producto::findOrFail(intval($product['id_product']));//Se obtiene el objeto producto por el id
                $producto->product_stock = ($producto->product_stock - intval($product['product_amount_sail']));

                if(!$producto->save()){
                    $orden = Order::find($id_order);
                    $orden->delete();

                    $ordenDetalle = OrderDetail::find($orden_detalle->id_order_detail);
                    $ordenDetalle->delete();

                    $ordenOrdenDetalle = OrderOrderDetail::find($orden_orden_detalle->id_order_order_detail);
                    $ordenOrdenDetalle->delete();

                    $inventarioE = InventaryE::find($inventario_e->id_inventory_e);
                    $inventarioE->delete();

                    return response()->json([
                        'message' => 'Ocurrio un error al actualizar el nuevo stock del producto',
                        'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                    ]);
                }
            }

            if(ShoppingCart::query()->where('id_user', '=', $request->id_user)->update(['shopping_cart_status' =>  $_ENV['STATUS_OFF']])){
                return response()->json([
                    'message' => 'Orden generada con exito',
                    'status' => $_ENV['CODE_STATUS_OK']
                ]);
            }else{
                return response()->json([
                    'message' => 'Ocurrio un error interno en el servidor',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }
        }
        else{
            return response()->json([
                'message' => 'Ocurrio un error interno al crear la orden',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

    public function getOrderByUser(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0'
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

        static::$id_user_global = $request->id_user;

        /*$ordenes = OrderOrderDetail::with('orderDetail')
        ->whereHas('order', function (Builder $query) {
            $query->where('order.id_order', '=', 11);
            })->orderBy('create_date', 'desc')->get();

        return $ordenes;*/

        $ordenes = OrderOrderDetail::query()
        ->with((['order.user' => function ($query) {
            $query->select('id_user', 'user_name', 'user_lastName', 'email', 'user_document', 'user_phone');
        }]))
        ->with((['orderDetail.address' => function ($query) {
            $query->select('id_address', 'user_address', 'address_description', 'address_status');
        }]))
        ->with((['order.orderStatus' => function ($query) {
            $query->select('id_order_status', 'order_status_description');
        }]))
        ->with((['orderDetail' => function ($query) {
            $query->select('id_order_detail', 'id_product', 'order_detail_quantity', 'order_detail_discount', 'order_detail_subtotal', 'order_detail_iva', 'order_detail_total');
        }]))
        ->with((['orderDetail.producto' => function ($query) {
            $query->select('id_product', 'id_provider', 'id_product_unit', 'product_name', 'product_code', 'product_description', 'product_price', 'product_rating');
        }]))
        ->with((['orderDetail.producto.provider' => function ($query) {
            $query->select('id_provider', 'provider_identification', 'provider_name', 'provider_address', 'provider_email', 'provider_phone');
        }]))
        ->with((['orderDetail.producto.productUnit' => function ($query) {
            $query->select('id_product_unit', 'name_product_unit', 'description_product_unit');
        }]))
        ->with((['order.typePay' => function ($query) {
            $query->select('id_pay', 'pay_description');
        }]))
        ->whereHas('order', function (Builder $query) {
            $query->where('id_user', '=', static::$id_user_global);
            })->orderBy('create_date', 'desc')->get();

        return $ordenes;
    }

    public function getOrderBySeller(){
        $ordenes = OrderOrderDetail::with('order.user', 'order.orderStatus', 'order.typePay', 'orderDetail', 'orderDetail.address', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit')
        /*->whereHas('order', function (Builder $query) {
            $id_order_status_pending = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_PENDING'])->pluck('id_order_status')->first();

            if(!isset($id_order_status_pending)){
                return response()->json([
                    'message' => 'Ocurrio un error interno en el servidor',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $query->where('id_order_status', '=', $id_order_status_pending);
            })->orderBy('create_date', 'desc')*/->get();

        return $ordenes;
    }

    public function changeStatusOrder(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_order' => 'required|numeric|min:0|not_in:0'
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

        $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();
        if(!isset($id_order_status_completed)){
            return response()->json([
                'message' => 'Ocurrio un error al intentar obtener el status completado de la orden',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
        $orden = Order::find($request->id_order);
        $orden->id_order_status = $id_order_status_completed;
        if($orden->save()){

        $result = DB::select("select TIMESTAMPDIFF(DAY, TIMESTAMP(create_date), updated_at) AS days,
        MOD(TIMESTAMPDIFF(HOUR, TIMESTAMP(create_date), updated_at), 24) AS hours,
        MOD(TIMESTAMPDIFF(MINUTE, TIMESTAMP(create_date), updated_at), 60) AS minutes
        from `order`
        where `order`.`id_order` = $orden->id_order ;");

        $result = $result['0'];

        $orden = Order::find($request->id_order);
        $orden->delivery_day = $result->days;
        $orden->delivery_hour = $result->hours;
        $orden->delivery_minute = $result->minutes;
        $orden->save();

            return response()->json([
                'message' => 'Orden completada',
                'status' => $_ENV['CODE_STATUS_OK'],
                'tiempo_despacho' => [
                    'dias' => $result->days,
                    'horas' => $result->hours,
                    'minutos' => $result->minutes
                ]
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

    public function getAllSales(Request $request){
        $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();
        if(isset($request->start_date) && isset($request->end_date)){

            $fecha_inicio = new DateTime($request->start_date);
            $fecha_inicio = $fecha_inicio->format('Y-m-d H:i:s');
            $fecha_fin = new DateTime($request->end_date);
            $fecha_fin = $fecha_fin->format('Y-m-d H:i:s');

            $ventas = Order::where('id_order_Status', $id_order_status_completed)
            ->whereBetween('updated_at', [$fecha_inicio, $fecha_fin])->get();
            return response()->json([
                'message' => 'Consulta realizada con exito',
                'status' => $_ENV['CODE_STATUS_OK'],
                'count' => count($ventas)
            ]);
        }
        $ventas = Order::where('id_order_Status', $id_order_status_completed)->get();
        return response()->json([
            'message' => 'Consulta realizada con exito',
            'status' => $_ENV['CODE_STATUS_OK'],
            'count' => count($ventas)
        ]);
    }

    public function getAllOrdersByStatus(Request $request){
        $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();
        $id_order_status_pending = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_PENDING'])->pluck('id_order_status')->first();
        $data = [];

        if(isset($request->start_date) && isset($request->end_date)){

            $fecha_inicio = new DateTime($request->start_date);
            $fecha_inicio = $fecha_inicio->format('Y-m-d H:i:s');
            $fecha_fin = new DateTime($request->end_date);
            $fecha_fin = $fecha_fin->format('Y-m-d H:i:s');

            $ordenesCompletadas = Order::where('id_order_Status', $id_order_status_completed)
            ->whereBetween('updated_at', [$fecha_inicio, $fecha_fin])->get();
            array_push($data, array ("id_order_Status" => $id_order_status_completed, "order_status_description" => $_ENV['ORDEN_COMPLETED'], "count"=> count($ordenesCompletadas) ) );

            $ordenesPendientes = Order::where('id_order_Status', $id_order_status_pending)
            ->whereBetween('updated_at', [$fecha_inicio, $fecha_fin])->get();
            array_push($data, array ("id_order_Status" => $id_order_status_pending, "order_status_description" => $_ENV['ORDEN_PENDING'], "count"=> count($ordenesPendientes) ) );

            return response()->json([
                'message' => 'Consulta realizada con exito',
                'status' => $_ENV['CODE_STATUS_OK'],
                'data' => $data
            ]);
        }

        $ordenesCompletadas = Order::where('id_order_Status', $id_order_status_completed)->get();
        array_push($data, array ("id_order_Status" => $id_order_status_completed, "order_status_description" => $_ENV['ORDEN_COMPLETED'], "count"=> count($ordenesCompletadas) ) );

        $ordenesPendientes = Order::where('id_order_Status', $id_order_status_pending)->get();
        array_push($data, array ("id_order_Status" => $id_order_status_pending, "order_status_description" => $_ENV['ORDEN_PENDING'], "count"=> count($ordenesPendientes) ) );

        return response()->json([
            'message' => 'Consulta realizada con exito',
            'status' => $_ENV['CODE_STATUS_OK'],
            'data' => $data
        ]);
    }

    public function getTypePayByOrder(Request $request){
        $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();
        $id_pago_paypal = TypePay::where('pay_description', '=', $_ENV['TYPE_PAY_PAYPAL'])->pluck('id_pay')->first();
        $id_pago_credit_card = TypePay::where('pay_description', '=', 'Tarjeta de Crédito/Débito')->pluck('id_pay')->first();

        $anioMes = date('Y-m');
        if(isset($request->fecha_inicio) && isset($request->fecha_fin)){
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;

            $ordenesPaypal = Order::whereBetween('updated_at', [$fecha_inicio, $fecha_fin])
            ->where('id_order_status', $id_order_status_completed)->where('id_pay', $id_pago_paypal)
            ->get();

            $ordenesCreditCard = Order::whereBetween('updated_at', [$fecha_inicio, $fecha_fin])
            ->where('id_order_status', $id_order_status_completed)->where('id_pay', $id_pago_credit_card)
            ->get();

            $data = ['PayPal' => count($ordenesPaypal), 'Tarjeta de Crédito/Débito' => count($ordenesCreditCard)];

            return response()->json([
                'message' => 'Consulta realizada con exito',
                'status' => $_ENV['CODE_STATUS_OK'],
                'data' => $data
            ]);

        }

        $ordenesPaypal = Order::orWhere('updated_at', 'like', $anioMes . '%')
        ->where('id_order_status', $id_order_status_completed)->where('id_pay', $id_pago_paypal)
        ->get();

        $ordenesCreditCard = Order::where('updated_at', 'like', $anioMes . '%')
        ->where('id_order_status', $id_order_status_completed)->where('id_pay', $id_pago_credit_card)
        ->get();

        $data = ['PayPal' => count($ordenesPaypal), 'Tarjeta de Crédito/Débito' => count($ordenesCreditCard)];

        return response()->json([
            'message' => 'Consulta realizada con exito',
            'status' => $_ENV['CODE_STATUS_OK'],
            'data' => $data
        ]);
    }

    public function getSalesByDate(Request $request){
        $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();

        $anioMes = date('Y-m');
        if(isset($request->fecha_inicio) && isset($request->fecha_fin)){
            $fecha_inicio = $request->fecha_inicio;
            $fecha_fin = $request->fecha_fin;

            $ordenes = Order::whereBetween('updated_at', [$fecha_inicio, $fecha_fin])
            ->where('id_order_status', $id_order_status_completed)->get();

            $data = ['Ventas' => count($ordenes)];

            return response()->json([
                'message' => 'Consulta realizada con exito',
                'status' => $_ENV['CODE_STATUS_OK'],
                'data' => $data
            ]);

        }

        $ordenes = Order::orWhere('updated_at', 'like', $anioMes . '%')
        ->where('id_order_status', $id_order_status_completed)->get();

        $data = ['Ventas' => count($ordenes)];

        return response()->json([
            'message' => 'Consulta realizada con exito',
            'status' => $_ENV['CODE_STATUS_OK'],
            'data' => $data
        ]);
    }
}
