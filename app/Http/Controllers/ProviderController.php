<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $proveedores = Provider::all()->where("provider_status","=",$_ENV['STATUS_ON']);
        return $proveedores;
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
                'id_type_provider' => 'required',
                'provider_qualified' => 'required',
                'provider_identification' => 'required',
                'provider_name' => 'required',
                'providerr_address' => 'required',
                'provider_email' => 'required | email',
                'provider_products_offered' => 'required',
                'provider_phone' => 'required',
                'provider_landline' => 'required',
                'provider_web_page' => 'required',
                'provider_person_name' => 'required',
                'provider_person_lastName' => 'required',
                'provider_transport' => 'required',
                'provider_response_time' => 'required',
                'provider_status' => 'required',
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

        $proveedor =  new Provider();
        $proveedor->id_type_provider = $request->id_type_provider;
        $proveedor->provider_qualified = $request->provider_qualified;
        $proveedor->provider_identification = $request->provider_identification;
        $proveedor->provider_name = $request->provider_name;
        $proveedor->providerr_address = $request->providerr_address;
        $proveedor->provider_email = $request->provider_email;
        $proveedor->provider_products_offered = $request->provider_products_offered;
        $proveedor->provider_phone = $request->provider_phone;
        $proveedor->provider_landline = $request->provider_landline;
        $proveedor->provider_web_page = $request->provider_web_page;
        $proveedor->provider_person_name = $request->provider_person_name;
        $proveedor->provider_person_lastName = $request->provider_person_lastName;
        $proveedor->provider_transport = $request->provider_transport;
        $proveedor->provider_response_time = $request->provider_person_lastName;
        $proveedor->provider_status = $request->provider_status;

        $proveedor->save();

        if(isset($proveedor->id_provider)){
            return response()->json([
                'message' => 'Proveedor creado con exito',
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
