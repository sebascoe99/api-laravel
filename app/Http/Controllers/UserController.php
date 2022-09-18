<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = User::with('roleUser')->whereNotIn('id_role', [$_ENV['CODE_ROL_CLIENT']])->orderBy('create_date', 'desc')->get();
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
                'id_role' => 'required|numeric|min:0|not_in:0',
                'id_identification_type' => 'required|numeric|min:0|not_in:0',
                'user_name' => 'required',
                'user_lastName' => 'required',
                'email' => 'required|email',
                'user_document' => 'required',
                'password' => 'required',
                'user_phone' => 'required',
                'user_address' => 'required',
                //'address_description' => 'required'
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
        $user->id_identification_type = intval($request->id_identification_type);
        $user->user_name = $request->user_name;
        $user->user_lastName = $request->user_lastName;
        $user->email = $request->email;
        $user->user_document = $request->user_document;
        $user->password = Hash::make($request->password);
        $user->password_encrypt = Crypt::encryptString($request->password);
        $user->user_phone = $request->user_phone;
        $user->user_address = $request->user_address;
        $user->user_status = $_ENV['STATUS_ON'];

        $user->save();
        //$token = $user->createToken('auth_token')->plainTextToken;

        if(isset($user->id_user)){

            $address = new Address();
            $address->id_user = $user->id_user;
            $address->user_address = $request->user_address;
            $address->address_status = $_ENV['STATUS_ON'];
            $address->address_description  = $request->address_description;
            $address->save();

            if(isset($address->id_address)){

                return response()->json([
                    'message' => 'Usuario creado exitosamente',
                    'status' => $_ENV['CODE_STATUS_OK']
                ]);
            }
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
                'id_role' => 'required|numeric|min:0|not_in:0',
                'id_identification_type' => 'required|numeric|min:0|not_in:0',
                'user_name' => 'required',
                'user_lastName' => 'required',
                'user_phone' => 'required',
                //'user_address' => 'required'
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

        if(isset($request->password)){
            $user->password = Hash::make($request->password);
            $user->password_encrypt = Crypt::encryptString($request->password);
        }

        $user->user_phone = $request->user_phone;
        //$user->user_address = $request->user_address;
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

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
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
                    'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
                ]);
        }

        $user = User::findOrFail($request->id);
        $user->password = Hash::make($request->password);
        $user->password_encrypt = Crypt::encryptString($request->password);
        if(property_exists($request, 'is_link')){
            $user->is_link = $_ENV['STATUS_ON'];
        }

        if($user->save()){
            return response()->json([
                'message' => 'Contraseña actualizado exitosamente',
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

    public function changePasswordByLink(Request $request)
    {
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
                    'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
                ]);
        }
        if(!(isset($request->link))){
            return response()->json([
                'message' => 'Debe proporcionar el link de recuperación de contraseña',
                'status' => $_ENV['CODE_STATUS_ERROR_CLIENT']
            ]);
        }

        $user = User::findOrFail($request->id_user);

        $mainController = new MailController();
        $mainController->sendEmailRecoverPassword($user->email, $request->link);

        return response()->json([
            'message' => 'Correo de recuperación de clave enviado con éxito',
            'status' => $_ENV['CODE_STATUS_OK']
        ]);
    }
}
