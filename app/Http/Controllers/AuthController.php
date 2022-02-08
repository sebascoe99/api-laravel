<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request){
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
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada con éxito',
            'status' => $_ENV['CODE_STATUS_OK']
        ]);
    }
}
