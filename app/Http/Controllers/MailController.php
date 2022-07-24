<?php

namespace App\Http\Controllers;

use App\Mail\Mailte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{
    public function sendEmail(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required'
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

        $details = [
            'title' => '¡Gracias! Tu pedido ha sido confirmado.',
            'body' => '¡Guau! Su pedido está en camino.'
        ];

        Mail::to($request->email)->send(new Mailte($details));

        return response()->json([
            'message' => 'Correo enviado',
            'status' => $_ENV['CODE_STATUS_OK']
        ]);
    }
}
