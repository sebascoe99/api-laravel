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

        $orden = new Order();
        $orden->id_user = $request->id_user;
        $orden->id_order_status = $id_order_status_pending;
        $orden->order_price_total = $request->order_price_total;
        $orden->voucher_number = $vaucher_present;

        if($orden->save()){
            $id_order = $orden->id_order;
            $id_pago_paypal = TypePay::where('pay_description', '=', $_ENV['TYPE_PAY_PAYPAL'])->pluck('id_pay')->first();

            if(!isset($id_pago_paypal)){
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
                $orden_detalle->id_pay = $id_pago_paypal;
                $orden_detalle->order_detail_quantity = intval($product['product_amount_sail']);
                if(array_key_exists('product_offered', $product)){
                    $orden_detalle->order_detail_discount = $product['product_offered'];
                }else{
                    $orden_detalle->order_detail_discount = 0;
                }
                $orden_detalle->order_detail_subtotal = ($product['product_price'] * intval($product['product_amount_sail']));
                $orden_detalle->order_detail_iva = 12;
                $orden_detalle->order_detail_total = ($product['product_price'] * intval($product['product_amount_sail']));

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

        $ordenes = OrderOrderDetail::with('order.user', 'order.orderStatus', 'orderDetail', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit', 'orderDetail.typePay')
        ->whereHas('order', function (Builder $query) {
            $query->where('id_user', '=', static::$id_user_global);
            })->orderBy('create_date', 'desc')->get();

        return $ordenes;
    }

    public function getOrderBySeller(){
        $ordenes = OrderOrderDetail::with('order.user', 'order.orderStatus', 'orderDetail', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit', 'orderDetail.typePay')
        ->whereHas('order', function (Builder $query) {
            $id_order_status_pending = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_PENDING'])->pluck('id_order_status')->first();

            if(!isset($id_order_status_pending)){
                return response()->json([
                    'message' => 'Ocurrio un error interno en el servidor',
                    'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
                ]);
            }

            $query->where('id_order_status', '=', $id_order_status_pending);
            })->orderBy('create_date', 'desc')->get();

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
            return response()->json([
                'message' => 'Orden completada',
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
