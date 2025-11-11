<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    public function registerProduct(Request $request){
        $productData = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:200',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0|max:999999.99',
            'supplier_id' => 'required|exists:suppliers,id'
        ]);

        if ($productData->fails()){
            return response()->json([
                'result' => false,
                'msg' => "Error de validacion.",
                'data' => $productData->errors()
            ], 422);
        }

        $validated = $productData->validated();

        try {
            $product = Product::create($validated);
        }
        catch (\Exception $e){
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al agregar el producto.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'msg' => "Se agrego correctamente el producto",
        ], 201);
    }

    public function getProducts(Request $request){
        # Query params
        $productID = $request->query('product_id');
        $supplierID = $request->query('supplier_id');

        # With product param product_id: http://localhost:8000/api/products?product_id=1
        if ($productID){
            $product = Product::find($productID);

            if (!$product){
                return response()->json([
                    'result' => false,
                    'msg' => "Producto no encontrado",
                    'data' => null
                ], 404);
            }

            return response()->json([
                'result' => true,
                'msg' => "Producto encontrado",
                'data' => new ProductResource($product)
            ], 200);
        }

        # With product param supplier_id: http://localhost:8000/api/products?supplier_id=1
        if ($supplierID){
            $products = Product::where('supplier_id', $supplierID)->get();

            if ($products->isEmpty()){
                return response()->json([
                    'result' => false,
                    'msg' => "No se encontraron productos de este proveedor",
                    'data' => []
                ], 404);
            }

            return response()->json([
                'result' => true,
                'msg' => "Productos encontrados",
                'data' => ProductResource::collection($products)
            ], 200);
        }

        # Without params
        $products = Product::all();

        return response()->json([
            'result' => true,
            'msg' => "Se trajeron todos los productos existentes",
            'data' => ProductResource::collection($products)
        ]);
    }

    public function productDeregister(Request $request){
        $request->validate([
            'id' => 'required|integer|exists:products,id'
        ]);

        $product = Product::find($request->id);

        if (!$product) {
            return response()->json([
                'result' => false,
                'msg' => 'El producto no existe o ya fue eliminado.'
            ], 404);
        }

        try {
            $product->delete();

            return response()->json([
                'result' => true,
                'msg' => 'El producto fue dado de baja correctamente.',
                'deleted_id' => $request->id
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al eliminar el producto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
