<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ProductCloud;
use App\Models\Inventory\CategoriesCloud;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductCloudResource;
use App\Models\Inventory\BranchCloud;
use App\Models\User\Cart;

class ProductsCloudController extends Controller
{
    public function checkProductsExistence(Request $request)
    {
        $productCode = $request->input('product_code');

        if (!$productCode) {
            return response()->json([
                'result' => false,
                'msg' => 'No se proporcionó un código de producto válido.',
                'data' => null
            ], 400);
        }

        $product = ProductCloud::where('product_code', $productCode)->first();
        
        if ($product) {
            return response()->json([
                'result' => true,
                'msg' => 'Existe el producto',
                'data' => $product
            ]);
        } else {
            return response()->json([
                'result' => false,
                'msg' => 'No existe el producto',
                'data' => null
            ]);
        }
    }

    public function registerProduct(Request $request){
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:15',
            'product_name' => 'required|string|max:50',
            'product_url_image' => 'required|string|max:255',
            'product_description' => 'required|string|max:1000',
            'product_stock' => 'required|numeric|min:0',
            'product_price' => 'required|numeric|min:0',
            'category_id' => 'required|string|max:50',
            'branch_id' => 'required|integer',
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
        $productCode = $request->query('product_code');
        $categoryID = $request->query('category_id');

        # With product param product_code: http://localhost:8000/api/customers/products?product_code=1
        if ($productCode){
            $product = ProductCloud::where('product_code', $productCode)->first(); # Solo toma el primer producto que coincida con el codigo

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
                'data' => [
                    'product_code' => $product->product_code,
                    'product_name' => $product->product_name,
                    'product_url_image' => $product->product_url_image,
                    'product_description' => $product->product_description,
                    'product_price' => $product->product_price,
                    'category_id' => $product->category_id,
                ]
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
                'data' => ProductCloudResource::collection($products)
            ], 200);
        }

        # Without params
        $products = ProductCloud::all();

        return response()->json([
            'result' => true,
            'msg' => "Se trajeron todos los productos existentes",
            'data' => ProductCloudResource::collection($products)
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

    public function createCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string|max:50',
            'category_name' => 'required|string|max:50',
        ]);

        $category = CategoriesCloud::where('category_name', $request->category_name)->first();
        if ($category) {
            return response()->json([
                'result' => false,
                'msg' => 'La categoria ya existe',
                'data' => null
            ], 409);
        }

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación',
                'data' => $validator->errors()
            ]);
        }

        $category = CategoriesCloud::create($validator->validated());
        return response()->json([
            'result' => true,
            'msg' => 'Categoria creada exitosamente',
        ]);
    }

    public function getAllCategories(){
        $categories = CategoriesCloud::all();
        return response()->json([
            'result' => true,
            'msg' => 'Categorias obtenidas exitosamente',
            'data' => $categories->map(function($category) {
                return [
                    'id' => $category->category_id,
                    'category_name' => $category->category_name
                ];
            })
        ], 200);
    }

    public function getCart(){

        $cart = Cart::where('customer_id', auth()->id())->first();
        if (!$cart) {
            return response()->json([
                'result' => false,
                'msg' => 'Carrito no encontrado',
                'data' => null
            ], 404);
        }
        return response()->json([
            'result' => true,
            'msg' => 'Carrito obtenido exitosamente',
            'data' => $cart
        ], 200);
    }

    public function addProductToCart(Request $request){
        $cart = Cart::where('customer_id', auth()->id())->first();
        if ($cart) {
            // Add item to array (simplified logic)
            $items = $cart->items ?? [];
            $items[] = ['product_id' => $request->product_id, 'quantity' => 1];
            $cart->items = $items;
            $cart->save();
        
            return response()->json(['result' => true, 'msg' => 'Agregado', 'data' => $cart]);
        }
    }
    
    public function removeProductFromCart(Request $request){
        $cart = Cart::where('customer_id', auth()->id())->first();
        if ($cart) {
            $cart->items()->detach($request->product_id);
            return response()->json([
                'result' => true,
                'msg' => 'Producto removido del carrito exitosamente',
                'data' => $cart
            ], 200);
        }
        return response()->json([
            'result' => false,
            'msg' => 'Carrito no encontrado',
            'data' => null
        ], 404);
    }

    public function createCart(Request $request){
        $cart = Cart::where('customer_id', auth()->id())->first();
        if ($cart) {
            return response()->json([
                'result' => false,
                'msg' => 'Carrito ya existe',
                'data' => null
            ], 409);
        }
        $cart = Cart::create([
            'customer_id' => auth()->id(),
            'items' => $request->items,
        ]);
        return response()->json([
            'result' => true,
            'msg' => 'Carrito creado exitosamente',
            'data' => $cart
        ], 200);
    }
}
