<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use App\Http\Resources\AdminBranchesResource;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User\User;
use App\Models\User\Person;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\BranchCloud;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function assignBranch(Request $request)
    {
        $request->validate([
            'curp' => 'required|string|exists:persons,CURP',
        ]);

        // Obtener la sucursal registrada
        $branch = Branch::first();

        if (!$branch) {
            return response()->json([
                'result' => false,
                'message' => 'No hay ninguna sucursal registrada en el sistema.'
            ], 404);
        }

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

        $user->branch_id = $branch->id;
        $user->save();

        return response()->json([
            'result' => true,
            'message' => 'Sucursal asignada correctamente al usuario.'
        ], 200);
    }

    public function getBranchByActivationKey ($key){
        $branchData = BranchCloud::where('activation_key', $key)->first();

        if (!$branchData) {
            return response()->json([
                'result' => false,
                'message' => 'Llave de activación no válida.'
            ], 404);
        }

        return response()->json([
            'result' => true,
            'message' => 'Sucursal obtenida correctamente',
            'data' => $branchData->id
        ], 200);
    }

    public function activateBranchKey($id){
        if ($id == null) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación.',
            ], 422);
        }

        try {
            $branch = BranchCloud::find($id);

            if (!$branch) {
                return response()->json([
                    'result' => false,
                    'message' => 'Sucursal no encontrada.'
                ], 404);
            }

            if ($branch->is_active) {
                return response()->json([
                    'result' => false,
                    'message' => 'La sucursal ya está activa.'
                ], 400);
            }

            $branch->is_active = true;
            $branch->save();

            return response()->json([
                'result' => true,
                'message' => 'Sucursal activada correctamente.',
                'data' => $branch
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al activar la sucursal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createCloudBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branchName' => 'required|string|max:50',
            'address' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lastBranch = BranchCloud::orderBy('branch_id', 'desc')->first();
        $newBranchId = $lastBranch ? $lastBranch->branch_id + 1 : 10;

        $activationKey = 'SUC-' . strtoupper(Str::random(6));

        try {
            $branch = BranchCloud::create([
                'branch_id' => $newBranchId,
                'branchName' => $request->branchName,
                'address' => $request->address,
                'activation_key' => $activationKey,
                'is_active' => false,
            ]);

            return response()->json([
                'result' => true,
                'msg' => 'Sucursal creada en la Nube.',
                'data' => [
                    'branchName' => $branch->branchName,
                    'branch_id' => $branch->branch_id,
                    'activation_key' => $branch->activation_key
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function registerBranchInPI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activation_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación.',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar si ya existe una sucursal registrada
            $existingBranch = Branch::first();
            if ($existingBranch) {
                return response()->json([
                    'result' => false,
                    'msg' => 'Ya existe una sucursal registrada. Elimina la actual antes de crear una nueva'
                ], 409);
            }

            // 1. Llamar a la API para obtener el ID de la sucursal por la llave
            $cloudApiBaseUrl = env('CLOUD_API_BASE_URL');
            $cloudGetBranchResponse = env('CLOUD_GET_BRANCH_RESPONSE');
            $cloudActivateBranchKey = env('CLOUD_ACTIVATE_BRANCH_KEY');
            $getBranchResponse = Http::get("{$cloudApiBaseUrl}/{$cloudGetBranchResponse}/{$request->activation_key}");

            if (!$getBranchResponse->successful() || !$getBranchResponse->json('result')) {
                return response()->json([
                    'result' => false,
                    'msg' => 'Llave de activación no válida o sucursal no encontrada.',
                    'error' => $getBranchResponse->json('message')
                ], 404);
            }

            $branchId = $getBranchResponse->json('data');

            // 2. Llamar a la API para activar la sucursal y obtener sus datos completos
            $activateResponse = Http::patch("{$cloudApiBaseUrl}/{$cloudActivateBranchKey}/{$branchId}");


            if (!$activateResponse->successful() || !$activateResponse->json('result')) {
                return response()->json([
                    'result' => false,
                    'msg' => 'Error al activar la sucursal.',
                    'error' => $activateResponse->json('message')
                ], $activateResponse->status());
            }

            $branchData = $activateResponse->json('data');

            // 3. Crear la sucursal localmente en el modelo Branch
            $branch = Branch::create([
                'id' => $branchData['branch_id'],
                'branchName' => $branchData['branchName'],
                'address' => $branchData['address'],
            ]);

            return response()->json([
                'result' => true,
                'msg' => 'Sucursal dada de alta exitosamente.',
                'data' => $branch->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al registrar la sucursal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getBranchesData(Request $request){
        $branchIds = $request->query('branch_ids');

        if (!is_array($branchIds) || empty($branchIds)) {
            return response()->json([
                'result' => false,
                'msg' => "branch_ids debe ser un arreglo de IDs."
            ], 422);
        }

        $branches = BranchCloud::whereIn('branch_id', $branchIds)->get(['branch_id', 'branchName', 'address']);

        return response()->json([
            'result' => true,
            'msg' => "Se obtuvo la información de las sucursales.",
            'data' => $branches
        ]);
    }

    public function getBranches(){
        $branchesInAdmin = BranchCloud::where('is_active', true)->get();
        return response()->json([
            'result' => true,
            'msg' => "Se obtuvo la información de las sucursales.",
            'data' => AdminBranchesResource::collection($branchesInAdmin)
        ]);
    }
}
