<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User\Customer;

class CustomersController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'phone' => 'nullable|string|digits:10',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'result' => true,
            'msg' => 'Registro completado correctamente'
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = $customer->createToken('auth_token')->plainTextToken;

        $isProduction = app()->environment('production');
        $domain = $isProduction ? '.tojosystemgroup.tech' : null;
        $secure = $isProduction;

        $cookie = cookie('token', $token, 60 * 24 * 7, '/', $domain, $secure, true, false, 'None');

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'customer' => $customer,
        ])->withCookie($cookie);
    }

    public function userData(Request $request){
        return response()->json([
            'result' => true,
            'msg' => "Sesión iniciada correctamente.",
            'user' => $request->user()
        ], 200);
    }

    public function loginCloudPruebas(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = $customer->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'customer' => $token,
        ]);
    }
}
