<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auth\User;
use App\Models\Auth\Person;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PersonResource;

class AccountController extends Controller
{
    public function registerPerson(Request $request)
    {
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

        $person = Person::create([
            'firstName' => $validatedData['firstName'],
            'lastName' => $validatedData['lastName'],
            'CURP' => Hash::make($validatedData['CURP']),
            'phoneNumber' => $validatedData['phoneNumber'],
        ]);

        return response()->json([
            'result' => true,
            'msg' => "Bienvenido " . $person->firstName . " a la plataforma.",
            'data' => new PersonResource($person)
        ], 201);
    }

    public function register(Request $request)
    {
        
    }

    public function login(Request $request)
    {
        
    }
}
