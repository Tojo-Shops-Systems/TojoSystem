<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function createBranch(Request $request){
        $branchData = Validator::make($request->all(), [
            'branchName' => 'required|string|max:50',
            'address' => 'required|string|max:200'
        ]);

        if ($branchData->fails()){
            return response()->json([
                'result' => false,
                'msg' => "Error de validacion.",
                'data' => $branchData->errors()
            ], 422);
        }

        $validated = $branchData->validated();

        try {
            $branch = Branch::create($validated);
        }
        catch (\Exception $e){
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al crear la sucursal.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'msg' => "Se creo correctamente la sucursal",
        ], 201);
    }
}
