<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Supplier;

class SuppliersController extends Controller
{
    public function registerSupplier(Request $request){
        $supplierData = Validator::make($request->all(), [
            'supplierName' => 'required|string|max:50',
            'contactEmail' => 'required|string|email|unique:users,email',
            'phoneNumber' => 'required|digits:10',
            'address' => 'required|string|max:100',
        ]);

        if ($supplierData->fails()){
            return response()->json([
                'result' => false,
                'msg' => "Error de validacion.",
                'data' => $supplierData->errors()
            ], 422);
        }

        $validated = $supplierData->validated();

        try {
            $supplier = Supplier::create($validated);
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
            'msg' => "Se dio de alta correctamente el proveedor",
        ], 201);
    }

    public function getSuppliers(Request $request){
        if (!$request->has('id')){
            $suppliers = Supplier::all();

            return response()->json([
                'result' => true,
                'msg' => 'Se obtuvieron todos los proveedores',
                'data' => SupplierResource::collection($suppliers)
            ]);
        }
        else {
            $supplierData = Supplier::find($request->id);

            if (!$supplierData){
                return response()->json([
                    'result' => false,
                    'msg' => "Proveedor no encontrado",
                    'data' => null
                ]);
            }

            return response()->json([
                'result' => true,
                'msg' => "Informacion encontrada",
                'data' => $supplierData
            ]);
        }
    }
}
