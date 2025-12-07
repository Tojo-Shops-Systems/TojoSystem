<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FeedBack;

class FeedbackController extends Controller
{
    public function registerData(Request $request){
        $validator = Validator::make($request->all(), [
            'sensorType' => 'required|string',
            'sensorNumber' => 'required|integer',
            'data' => 'required|string'
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
            $feedback = FeedBack::create([
                'sensorType' => $validated['sensorType'],
                'sensorNumber' => $validated['sensorNumber'],
                'data' => $validated['data'],
            ]);
        }
        catch (\Exception $e){
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al registrar el feedback.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'msg' => "Feedback registrado correctamente.",
        ], 201);
    }
}
