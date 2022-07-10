<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function register(Request $request){
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
                'user_address' => 'required',
                'address_description' => 'required'
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
        $user->id_role = $request->id_role;
        $user->id_identification_type = $request->id_identification_type;
        $user->user_name = $request->user_name;
        $user->user_lastName = $request->user_lastName;
        $user->email = $request->email;
        $user->user_document = $request->user_document;
        $user->password = Hash::make($request->password);
        $user->password_encrypt = Crypt::encryptString($request->password);
        $user->user_phone = $request->user_phone;
        //$user->user_address = $request->user_address;
        $user->user_status = $_ENV['STATUS_ON'];

        $user->save();
        //$token = $user->createToken('auth_token')->plainTextToken;

        if(isset($user->id_user)){

            $address = new Address();
            $address->id_user = $user->id_user;
            $address->user_address = $request->user_address;
            $address->address_status = $_ENV['STATUS_ON'];
            $address->address_description = $request->address_description;
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

    public function login(Request $request){
        if (!Auth::attempt($request->only('email', 'password') + ['user_status' => $_ENV['STATUS_ON']])){
            return response()->json([
                'message' => 'Credenciales Invalidas',
                'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
            ]);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        if(isset($token)){
            return response()->json([
                'message' => 'Credenciales Correctas',
                'status' => $_ENV['CODE_STATUS_OK'],
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);
        }
        return response()->json([
            'message' => 'Ocurrio un error interno en el servidor',
            'status' => $_ENV['CODE_STATUS_SERVER_ERROR']
        ]);
    }

    public function logout(Request $request){
        $user = User::where('id_user', $request->id)->firstOrFail();
        if($user->tokens()->delete()){
            return response()->json([
                'message' => 'Sesión cerrada con éxito',
                'status' => $_ENV['CODE_STATUS_OK']
            ]);
        }
        return response()->json([
            'message' => 'Debe iniciar sesión',
            'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
        ]);
    }

    public function infouser(Request $request){
        return $request->user();
    }
}
