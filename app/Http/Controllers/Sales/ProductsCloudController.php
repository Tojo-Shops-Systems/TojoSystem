<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ProductCloud;
use App\Models\Inventory\CategoriesCloud;
use Illuminate\Validation\Validator;

class ProductsCloudController extends Controller
{
    public function checkProductsExistence(Request $request)
    {
        $products = ProductCloud::whereIn('product_code', $request->product_codes)->get();
        
        if ($products->count() == $request->product_codes->count()) {
            return response()->json([
                'result' => true,
                'msg' => 'Existe el producto',
                'data' => $products
            ]);
        } else {
            return response()->json([
                'result' => false,
                'msg' => 'No existe el producto',
                'data' => null
            ]);
        }
    }

    public function getAllCategories()
    {
        $categories = CategoriesCloud::all();
        return response()->json([
            'result' => true,
            'msg' => 'Categorias obtenidas exitosamente',
            'data' => $categories
        ]);
    }

    public function registerProduct(Request $request){
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:15',
            'product_name' => 'required|string|max:50',
            'product_url_image' => 'required|string|max:255',
            'product_description' => 'required|string|max:200',
            'product_stock' => 'required|numeric|min:0',
            'product_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories_cloud,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación',
                'data' => $validator->errors()
            ]);
        }

        $product = ProductCloud::create($validator->validated());
        return response()->json([
            'result' => true,
            'msg' => 'Producto registrado exitosamente',
        ]);
    }

    public function getProducts(Request $request){
        # Query params
        $productID = $request->query('product_id');
        $categoryID = $request->query('category_id');

        # With product param product_id: http://localhost:8000/api/customers/products?product_id=1
        if ($productID){
            $product = ProductCloud::find($productID);

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
                'data' => $product
            ], 200);
        }

        # With product param category_id: http://localhost:8000/api/customers/products?category_id=1
        if ($categoryID){
            $products = ProductCloud::where('category_id', $categoryID)->get();

            if ($products->isEmpty()){
                return response()->json([
                    'result' => false,
                    'msg' => "No se encontraron productos de esta categoria",
                    'data' => []
                ], 404);
            }

            return response()->json([
                'result' => true,
                'msg' => "Productos encontrados",
                'data' => $products
            ], 200);
        }

        # Without params
        $products = ProductCloud::all();

        return response()->json([
            'result' => true,
            'msg' => "Se trajeron todos los productos existentes",
            'data' => $products
        ]);
    }

    public function getBranchHasSpecifyProduct(Request $request){
        $productCode = $request->query('product_code');
        
        $branchesHasProduct = ProductCloud::where('product_code', $productCode)->pluck('branch_id');

        return response()->json([
            'result' => true,
            'msg' => "Se trajeron todas las sucursales que tienen el producto",
            'data' => $branchesHasProduct
        ]);
    }
}
