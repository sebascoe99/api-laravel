<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShoppingCartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return "hola";
    }

    public function saveProductCard(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
                'id_product' => 'required|numeric|min:0|not_in:0',
                'product_offered' => 'required|numeric|min:0|not_in:0',
                'product_offered_price_total' => 'required|min:0|not_in:0',
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

        $carrito = new ShoppingCart();
        $carrito->id_user = $request->id_user;
        $carrito->id_product = $request->id_product;
        $carrito->shopping_cart_status = $_ENV['STATUS_ON'];
        if(isset($request->product_offered))
            $carrito->product_offered = $request->product_offered;
        if(isset($request->product_offered_price_total))
            $carrito->product_offered_price_total = $request->product_offered_price_total;


        if($carrito->save()){
            return response()->json([
                'message' => 'Guardado con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

    public function getProductCardByIdUser(Request $request)
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

        $carritoxUsuario = ShoppingCart::with('producto.category', 'producto.brand', 'user')->where("shopping_cart_status", "=", $_ENV['STATUS_ON'])
        ->where("id_user", "=", $request->id_user)
        ->orderBy('create_date', 'desc')->get();
        return $carritoxUsuario;
    }

    public function deleteOneProductInCard(Request $request){
        //return $request->all();
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
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

        if(ShoppingCart::query()->where('id_user', '=', $request->id_user)->where('id_product', '=', $request->id_product)->update(['shopping_cart_status' =>  $_ENV['STATUS_OFF']])){
            return response()->json([
                'message' => 'Eliminado con exito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }else{
            return response()->json([
                'message' => 'Ocurrio un error interno en el servidor',
                'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
            ]);
        }
    }

    public function deleteAllProductInCard(Request $request){
        //return $request->all();
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

        if(ShoppingCart::query()->where('id_user', '=', $request->id_user)->update(['shopping_cart_status' =>  $_ENV['STATUS_OFF']])){
            return response()->json([
                'message' => 'Eliminado con exito',
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
