<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request){

        /*$validatedData = $request->validate([
            'id_role' => 'required|max:11',
            'user_name' => 'required|string|max:100',
            'user_lastName' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:user',
            'user_document' => 'required|string|max:10',
            'password' => 'required|string|max:50',
            'user_phone' => 'required|string|max:10'
        ]);*/
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
        return $request;
        if (!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Invalid login details',
                'status' => 401
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
}
