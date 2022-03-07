<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $proveedores = Provider::with('type_provider', 'identification_type')->orderBy('create_date', 'desc')->get();;
        //Provider::all()->where("provider_status","=",$_ENV['STATUS_ON']);
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
                'id_user' => 'required|numeric|min:0|not_in:0',
                'id_type_provider' => 'required|numeric|min:0|not_in:0',
                'id_identification_type' => 'required|numeric|min:0|not_in:0',
                'provider_qualified' => 'required',
                'provider_identification' => 'required',
                'provider_name' => 'required',
                'provider_address' => 'required',
                'provider_email' => 'required | email',
                'provider_products_offered' => 'required',
                'provider_phone' => 'required',
                'provider_landline' => 'required',
                'provider_web_page' => 'required',
                'provider_person_name' => 'required',
                'provider_person_lastName' => 'required',
                'provider_response_time_day' => 'required',
                'provider_response_time_hour' => 'required',
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

        DB::enableQueryLog();
        $proveedor =  new Provider();
        $proveedor->id_type_provider = intval($request->id_type_provider);
        $proveedor->id_identification_type = intval($request->id_identification_type);
        $proveedor->provider_qualified = $request->provider_qualified;
        $proveedor->provider_identification = $request->provider_identification;
        $proveedor->provider_name = $request->provider_name;
        $proveedor->provider_address = $request->provider_address;
        $proveedor->provider_email = $request->provider_email;
        $proveedor->provider_products_offered = $request->provider_products_offered;
        $proveedor->provider_phone = $request->provider_phone;
        $proveedor->provider_landline = $request->provider_landline;
        $proveedor->provider_web_page = $request->provider_web_page;
        $proveedor->provider_person_name = $request->provider_person_name;
        $proveedor->provider_person_lastName = $request->provider_person_lastName;
        $proveedor->provider_response_time_day = $request->provider_response_time_day;
        $proveedor->provider_response_time_hour = $request->provider_response_time_hour;
        $proveedor->provider_status = $request->provider_status;

        $proveedor->save();

        if(isset($proveedor->id_provider)){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_INSERCION'];
            $audit->audit_description = 'Se agregó un nuevo proveedor'.' con nombre ' . $proveedor->provider_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROVIDER'];
            $audit->audit_query = $queryStr;
            $audit->save();

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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|numeric|min:0|not_in:0',
                'id_type_provider' => 'required|numeric|min:0|not_in:0',
                'id_identification_type' => 'required|numeric|min:0|not_in:0',
                'provider_qualified' => 'required',
                'provider_identification' => 'required',
                'provider_name' => 'required',
                'provider_address' => 'required',
                'provider_email' => 'required | email',
                'provider_products_offered' => 'required',
                'provider_phone' => 'required | max:15',
                'provider_landline' => 'required | max:10',
                'provider_web_page' => 'required',
                'provider_person_name' => 'required',
                'provider_person_lastName' => 'required',
                'provider_response_time_day' => 'required',
                'provider_response_time_hour' => 'required',
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

        DB::enableQueryLog();
        $proveedor = Provider::findOrFail($request->id);
        $proveedor->id_type_provider = intval($request->id_type_provider);
        $proveedor->id_identification_type = intval($request->id_identification_type);
        $proveedor->provider_qualified = $request->provider_qualified;
        $proveedor->provider_identification = $request->provider_identification;
        $proveedor->provider_name = $request->provider_name;
        $proveedor->provider_address = $request->provider_address;
        $proveedor->provider_email = $request->provider_email;
        $proveedor->provider_products_offered = $request->provider_products_offered;
        $proveedor->provider_phone = $request->provider_phone;
        $proveedor->provider_landline = $request->provider_landline;
        $proveedor->provider_web_page = $request->provider_web_page;
        $proveedor->provider_person_name = $request->provider_person_name;
        $proveedor->provider_person_lastName = $request->provider_person_lastName;
        $proveedor->provider_response_time_day = $request->provider_response_time_day;
        $proveedor->provider_response_time_hour = $request->provider_response_time_hour;
        $proveedor->provider_status = $request->provider_status;

        if($proveedor->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizo el proveedor'.' con nombre ' . $proveedor->provider_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROVIDER'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Proveedor actualizado con exito',
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
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

        DB::enableQueryLog();
        $proveedor = Provider::findOrFail($request->id);
        $proveedor->provider_status = $_ENV['STATUS_OFF'];
        if($proveedor->save()){
            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ELIMINACION'];
            $audit->audit_description = 'Se elimino el proveedor'.' con nombre ' . $proveedor->provider_name;
            $audit->audit_module = $_ENV['AUDIT_MODULE_PROVIDER'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Eliminado correctamente',
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
