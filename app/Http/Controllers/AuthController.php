<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function register(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'id_role' => 'required',
                'user_name' => 'required',
                'user_lastName' => 'required',
                'email' => 'required|email',
                'user_document' => 'required',
                'password' => 'required',
                'user_phone' => 'required',
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
        $user->user_name = $request->user_name;
        $user->user_lastName = $request->user_lastName;
        $user->email = $request->email;
        $user->user_document = $request->user_document;
        $user->password = Hash::make($request->password);
        $user->user_phone = $request->user_phone;

        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request){
        //return $request;
        if (!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Credenciales Invalidas',
                'status' => $_ENV['CODE_STATUS_ERROR_CREDENTIALS_CLIENT']
            ]);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function infouser(Request $request){
        return $request->user();
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
}
