<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = User::with('roleUsers')->whereNotIn('id_role', [$_ENV['CODE_ROL_CLIENT']])->orderBy('create_date', 'desc')->get();
        return $usuarios;
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
                'id_role' => 'required',
                'id_identification_type' => 'required',
                'user_name' => 'required',
                'user_lastName' => 'required',
                'email' => 'required|email',
                'user_document' => 'required',
                'password' => 'required',
                'user_phone' => 'required',
                'user_address' => 'required'
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
                    'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
                ]);
        }

        $user = new User();
        $user->id_role = intval($request->id_role);
        $user->id_identification_type = intval($request->id_type_identification);
        $user->user_name = $request->user_name;
        $user->user_lastName = $request->user_lastName;
        $user->email = $request->email;
        $user->user_document = $request->user_document;
        $user->password = Hash::make($request->password);
        $user->user_phone = $request->user_phone;
        $user->user_address = $request->user_address;
        $user->user_status = $_ENV['STATUS_ON'];

        $user->save();
        //$token = $user->createToken('auth_token')->plainTextToken;

        if(isset($user->id_user)){
            return response()->json([
                'message' => 'Usuario creado exitosamente',
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
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_role' => 'required',
                'id_identification_type' => 'required',
                'user_name' => 'required',
                'user_lastName' => 'required',
                'email' => 'required|email',
                'user_document' => 'required',
                'user_phone' => 'required',
                'user_address' => 'required'
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
                    'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
                ]);
        }

        $user = User::findOrFail($request->id);
        $user->id_role = intval($request->id_role);
        $user->id_identification_type = intval($request->id_identification_type);
        $user->user_name = $request->user_name;
        $user->user_lastName = $request->user_lastName;
        $user->email = $request->email;
        $user->user_document = $request->user_document;

        if(isset($request->password))
            $user->password = Hash::make($request->password);

        $user->user_phone = $request->user_phone;
        $user->user_address = $request->user_address;
        $user->user_status = $_ENV['STATUS_ON'];
        //$token = $user->createToken('auth_token')->plainTextToken;

        if($user->save()){
            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'status' => $_ENV['CODE_STATUS_OK'],
            ]);
        }
        return response()->json([
            'message' => 'Ocurrio un error interno en el servidor',
            'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->user_status = $_ENV['STATUS_OFF'];
        if($user->save()){
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
