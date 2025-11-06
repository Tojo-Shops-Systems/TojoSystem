<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\User;
use App\Models\User\Person;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PersonResource;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function registerPerson(Request $request)
    {
        /* EXPECTED DATA
        {
            "firstName": "Jaret Eduardo",
            "lastName": "Gonzalez Carrasco",
            "CURP": "AAAA000000AAAAAAA0",
            "phoneNumber": "1234567890"
        }
        */
        $validatedData = Validator::make($request->all(), [
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'CURP' => 'required|string|max:18',
            'phoneNumber' => 'required|digits:10',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'result' => false,
                'msg' => "Error de validacion.",
                'data' => $validatedData->errors()
            ], 422);
        }

        $validated = $validatedData->validated();

        try {
            $person = Person::create($validated);
        }
        catch (\Exception $e){
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al registrar a la persona.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'msg' => "Bienvenido " . $person->firstName . " a la plataforma.",
            'data' => new PersonResource($person)
        ], 201);
    }

    public function register(Request $request)
    {
        /* EXPECTED DATA
        {
            "userType": "admin",
            "email": "jaret@email.com",
            "password": "J@ret1234",
            "password_confirmation": "J@ret1234",
            "person_id": 1
        }
        */
        $validator = Validator::make($request->all(), [
            'userType' => 'required|string|max:15',
            'email' => 'required|string|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            ],
            'person_id' => 'required|exists:persons,id'
        ]);

        if ($validator->fails()){
            return response()->json([
                'result' => false,
                'msg' => "Error de validacion",
                'data' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try{
            $user = User::create($validated);
        }
        catch (\Exception $e){
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al registrar al usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'msg' => "Bienvenido a la plataforma.",
        ], 201);
    }

    public function login(Request $request)
    {
        /* EXPECTED DATA
        {
            "email": "jaret1234@email.com",
            "password": "J@ret1234"
        }
        */
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, false)){
            return response()->json([
                'result' => false,
                'msg' => "Credenciales incorrectas",
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'result' => false,
                'msg' => "Credenciales incorrectas",
            ], 401);
        }

        $person = Person::with('user')->findOrFail($user->person_id);

        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'result' => true,
            'msg' => "Credenciales Correctas",
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new PersonResource($person)
        ], 200);
    }

    public function logout(Request $request)
    {
        /* EXPECTED DATA
        Authorization: Bearer 1|eqCl12LTF3DWq6i0HKEIXoYh0QeFojql4uWbx399e983e9e6
        */
        $request->user()->currentAccessToken()->delete(); # It will display an error but it works correctly, ignore the error.

        return response()->json([
            'result' => true,
            'msg' => 'Sesión cerrada correctamente.'
        ], 200);
    }

    public function identifyPerson(Request $request)
    {
        /* EXPECTED DATA
        {
            "CURP": "AAAA000000AAAAAAA0"
        }
        */
        $validatedData = Validator::make($request->all(), [
            'CURP' => 'required|string|max:18'
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'result' => false,
                'msg' => "Error de validación.",
                'data' => $validatedData->errors()
            ], 422);
        }

        $validated = $validatedData->validated();

        try {
            $person = Person::where('CURP', $validated['CURP'])->first();

            if (!$person) {
                return response()->json([
                    'result' => false,
                    'msg' => "No se encontró ninguna persona con esa CURP."
                ], 404);
            }

            return response()->json([
                'result' => true,
                'msg' => "Persona identificada correctamente.",
                'data' => [
                    'id' => $person->id,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => "Error interno del servidor.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}