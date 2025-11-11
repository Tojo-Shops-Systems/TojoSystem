<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User\User;
use App\Models\User\Person;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function createBranch(Request $request){
        $existingBranch = Branch::first();

        if ($existingBranch){
            return response()->json([
                'result' => false,
                'msg' => 'Ya existe una sucursal registrada. Elimina la actual antes de crear una nueva'
            ], 409);
        }

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

    public function assignBranch(Request $request)
    {
        $request->validate([
            'curp' => 'required|string|exists:persons,CURP',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $person = Person::where('CURP', $request->curp)->first();

        if (!$person) {
            return response()->json([
                'result' => false,
                'message' => 'Persona no encontrada.'
            ], 404);
        }

        $user = User::where('person_id', $person->id)->first();

        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'Usuario asociado no encontrado.'
            ], 404);
        }

        $user->branch_id = $request->branch_id;
        $user->save();

        return response()->json([
            'result' => true,
            'message' => 'Sucursal asignada correctamente al usuario.'
        ], 200);
    }

    public function getBranch (Request $request){
        $branchData = Branch::all();

        return response()->json([
            'result' => true,
            'message' => 'Sucursal obtenida correctamente',
            'data' => BranchResource::collection($branchData)
        ], 200);
    }
}
