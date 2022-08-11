<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Iva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IvaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $iva = Iva::orderBy('create_date', 'desc')->get();
        return $iva;
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
                'porcent' => 'required'
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
        $actualizar = Iva::where('iva_status', $_ENV['STATUS_ON'])
                    ->update(['iva_status' => $_ENV['STATUS_OFF']]);

        $iva = new Iva();
        $iva->id_user = $request->id_user;
        $iva->porcent = $request->porcent;
        $iva->iva_status = $_ENV['STATUS_ON'];

        if($iva->save()){
            return response()->json([
                'message' => 'Iva guardado exitosamente',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }
        return response()->json([
            'message' => 'Ocurrio un error interno en el servidor',
            'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
        ]);
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
    public function edit(Request $request)
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
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'porcent' => 'required',
                'undefined_date' => 'required',
                'date_start' => 'required',
                'date_end' => 'required',
                'id_user' => 'required'
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
        $iva = Iva::find($request->id);
        $iva->porcent = $request->porcent;
        $iva->undefined_date = $request->undefined_date;
        $iva->date_start = $request->date_start;
        $iva->date_end = $request->date_end;

        if($iva->save()){

            foreach (DB::getQueryLog() as $q) {
                $queryStr = Str::replaceArray('?', $q['bindings'], $q['query']);
            }
            $audit =  new Audit();
            $audit->id_user = intval($request->id_user);
            $audit->audit_action = $_ENV['AUDIT_ACTION_ACTUALIZACION'];
            $audit->audit_description = 'Se actualizó el iva con porcentaje ' . $iva->porcent . '%';
            $audit->audit_module = $_ENV['AUDIT_MODULE_IVA'];
            $audit->audit_query = $queryStr;
            $audit->save();

            return response()->json([
                'message' => 'Iva actualizado con éxito',
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
    public function destroy($id)
    {
        //
    }
}
