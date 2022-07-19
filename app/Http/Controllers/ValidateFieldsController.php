<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Models\Producto;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
class ValidateFieldsController extends Controller
{
    /**
     * Validate field product code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validateProductCode(){
        $product_code = Producto::whereRaw('product_code = (select max(`product_code`) from product)')->pluck('product_code')->first();

        if(isset($product_code)){
            return response()->json([
                'message' => $product_code + 1,
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
     * Validate field product name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validateProductName(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'required',
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

        if(Producto::where('product_name', $request->product_name)->exists()){
            return response()->json([
                'message' => "Existe",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => "No existe",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }
    }

    /**
     * Validate field email from table user .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateUserEmail(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
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
        $email = User::where('email', $request->email)->first();

        if(isset($email)){
            return response()->json([
                'message' => "Existe",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => "No existe",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }
    }

     /**
     * Validate field user_document from table user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateUserIdentification(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'user_document' => 'required',
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

        $indentification = User::where('user_document', $request->user_document)->first();

        if(isset($indentification)){
            return response()->json([
                'message' => "Existe",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => "No existe",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }
    }

    /**
     * Validate field user_document from table user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateUserPassword(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required',
                'password' => 'required',
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

        $user = User::where('id_user', $request->id_user)->first();

        if(isset($user)){
            $decrypt = Crypt::decryptString($user->password_encrypt);

            if($decrypt == $request->password){
                return response()->json([
                    'message' => "Coincide",
                    'status' => $_ENV['CODE_STATUS_OK'],
                ]);
            }else{
                return response()->json([
                    'message' => "No Coincide",
                    'status' => $_ENV['CODE_STATUS_OK'],
                ]);
            }
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }

    }

    /**
     * Validate field id_product from table promotion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateProductInPromotion(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_product' => 'required|numeric|min:0|not_in:0',
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

        $producto_promocion = Promotion::where('id_product', $request->id_product)->where('promotion_status', $_ENV['STATUS_ON'])->first();

        if(isset($producto_promocion)){
            return response()->json([
                'message' => "Tiene promocion",
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'No tiene promocion',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }

    }

    public function validateAdrresInOrderPending(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_address' => 'required|numeric|min:0|not_in:0',
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

        $where = ['id_address' => $request->id_address];
        $response = OrderDetail::where($where)->get();
        $count = $response->count();
        
        if($count > 0){

            $id_order_status_pending = OrderStatus::where('order_status_description', '=', $_ENV['ORDEN_PENDING'])->pluck('id_order_status')->first();

            $where2 = ['order_status.id_order_status' => $id_order_status_pending,
                      'order_detail.id_order_detail' => $response['0']['id_order_detail']];

            $data = OrderStatus::select('*')
                ->join('order', 'order_status.id_order_status', '=', 'order.id_order_status')
                ->join('order_order_detail', 'order.id_order', '=', 'order_order_detail.id_order')
                ->join('order_detail', 'order_order_detail.id_order_detail', '=', 'order_detail.id_order_detail')
                ->where($where2)
                ->get();

            $count2 = $data->count();

            if($count2 > 0){
                return response()->json([
                    'message' => 'existe',
                    'status' => $_ENV['CODE_STATUS_OK']
                ]);
            }
        }

        return response()->json([
            'message' => 'no existe',
            'status' => $_ENV['CODE_STATUS_OK']
        ]);
    }

}
