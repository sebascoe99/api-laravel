<?php

namespace App\Http\Controllers;

use App\Models\InventaryE;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderOrderDetail;
use App\Models\OrderStatus;
use App\Models\Producto;
use App\Models\TypePay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

use function PHPUnit\Framework\isNull;

class InventaryEController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inventarioE = InventaryE::with('order', 'order.user', 'order.orderStatus', 'order.typePay', 'orderDetail', 'orderDetail.address', 'orderDetail.producto','orderDetail.producto.category')->whereHas('order', function (Builder $query) {
            $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();

            if(!isset($id_order_status_completed)){
                return response()->json([
                    'message' => 'Ocurrio un error interno en el servidor',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $query->where('id_order_status', '=', $id_order_status_completed);
            })->orderBy('create_date', 'desc')->get();

        return $inventarioE;
    }

    public function getOrderDetail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_order' => 'required|numeric|min:0|not_in:0',
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
        $orden = OrderOrderDetail::with('order.user', 'order.orderStatus', 'order.typePay', 'orderDetail', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit')->where('id_order', '=',  $request->id_order)->get();
        return $orden;
    }


    public function getOrderDetailStatusCompleted()
    {
        $ordenes = OrderOrderDetail::with('order.user', 'order.orderStatus', 'order.typePay', 'orderDetail', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit')->whereHas('order', function (Builder $query) {
            $id_order_status_completed = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_COMPLETED'])->pluck('id_order_status')->first();

            if(!isset($id_order_status_completed)){
                return response()->json([
                    'message' => 'Ocurrio un error interno en el servidor',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $query->where('id_order_status', '=', $id_order_status_completed);
            })->get();

        return $ordenes;
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
                'inventory_stock_amount' => 'required|between:0.01,9999.99',
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

        $id_pago_efectivo = TypePay::where('pay_description', '=', $_ENV['TYPE_PAY_CASH'])->pluck('id_pay')->first();

            if(!isset($id_pago_efectivo)){
                return response()->json([
                    'message' => 'Ocurrio un error interno al agregar el tipo de pago',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

        $orden = new Order();
        $orden->id_user = $request->id_user;
        $orden->id_order_status = $id_order_status_completed;
        $orden->id_pay = $id_pago_efectivo;
        $orden->order_price_total = 0;
        $orden->voucher_number = $vaucher_present;

        if($orden->save()){
            $id_order = $orden->id_order;

            $orden_detalle = new OrderDetail();
            $orden_detalle->id_product = $request->id_product;
            $orden_detalle->order_detail_quantity = 0;
            $orden_detalle->order_detail_discount = 0;
            $orden_detalle->order_detail_subtotal = 0;
            $orden_detalle->order_detail_iva = 0;
            $orden_detalle->order_detail_total = 0;

            if(!$orden_detalle->save()){
                return response()->json([
                    'message' => 'Ocurrio un error interno al crear la orden detalle',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $orden_orden_detalle = new OrderOrderDetail();
            $orden_orden_detalle->id_order = $id_order;
            $orden_orden_detalle->id_order_detail = $orden_detalle->id_order_detail;

            if(!$orden_orden_detalle->save()){
                return response()->json([
                    'message' => 'Ocurrio un error interno al crear la orden detalle maestro',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            if(isset($request->inventory_description) && !is_null($request->inventory_description)){
                $description = $request->inventory_description;
            }else{
                $description = $request->inventory_description_aux;
            }

            $inventario_e = new InventaryE();
            $inventario_e->id_order = $id_order;
            $inventario_e->id_order_detail = $orden_detalle->id_order_detail;
            $inventario_e->inventory_movement_type = $_ENV['INVENTORY_MOVEMENT_TYPE_EGRESO'];
            $inventario_e->inventory_stock_amount = $request->inventory_stock_amount;
            $inventario_e->inventory_description = $description;

            if(!$inventario_e->save()){
                return response()->json([
                    'message' => 'Ocurrio un error interno al crear la orden dentro del inventario de tipo egreso',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $producto = Producto::findOrFail($request->id_product);//Se obtiene el objeto producto por el id
            $producto->product_stock = ($producto->product_stock - $request->inventory_stock_amount);

            if(!$producto->save()){
                return response()->json([
                    'message' => 'Ocurrio un error al actualizar el nuevo stock del producto',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }


            return response()->json([
                'message' => 'Guardado con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }
        else{
            return response()->json([
                'message' => 'Ocurrio un error interno al crear la orden',
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
