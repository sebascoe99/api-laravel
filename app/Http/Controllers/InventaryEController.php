<?php

namespace App\Http\Controllers;

use App\Models\InventaryE;
use App\Models\OrderOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class InventaryEController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inventarioE = InventaryE::with('order', 'order.orderStatus')->orderBy('create_date', 'desc')->get();
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
        $orden = OrderOrderDetail::with('order.user', 'order.orderStatus', 'orderDetail', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit', 'orderDetail.typePay')->where('id_order', '=',  $request->id_order)->get();
        return $orden;
    }


    public function getOrderDetailStatusCompleted()
    {
        DB::enableQueryLog();
        $ordenes = OrderOrderDetail::with('order.user', 'order.orderStatus', 'orderDetail', 'orderDetail.producto', 'orderDetail.producto.provider', 'orderDetail.producto.productUnit', 'orderDetail.typePay')->whereHas('order', function (Builder $query) {
            $query->where('id_order_status', '=', $_ENV['ORDEN_COMPLETED']);
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
        //
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
