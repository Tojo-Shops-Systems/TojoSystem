<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Inventory\Categories;

class ProductsController extends Controller
{
    public function registerProduct(Request $request){
        $productData = Validator::make($request->all(), [
            'code' => 'required|string|max:15',
            'name' => 'required|string|max:50',
            'url_image' => 'required|string|max:255',
            'description' => 'required|string|max:200',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0|max:999999.99',
            'category_id' => 'nullable|integer',
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

    public function fastRegisterProduct(Request $request){
        $data = Validator::make($request->all(), [
            'code' => 'required|string|max:15',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0|max:999999.99',
            'supplier_id' => 'required|exists:suppliers,id'
        ]);

        if ($data->fails()){
            return response()->json([
                'result' => false,
                'msg' => "Error de validacion.",
                'data' => $data->errors()
            ], 422);
        }

        $validated = $data->validated();

        $url = env('GOUPC_URL') . $validated['code'];

        $response = Http::get($url);

        if ($response->failed()){
            return response()->json([
                'result' => false,
                'msg' => "Error al obtener el producto.",
                'data' => $response->json()
            ], $response->status());
        }

        $crawler = new Crawler($response->body());

        try {
            $name = $crawler->filter('.name nobr')->count()
                ? $crawler->filter('.name nobr')->text()
                : null;

            $description = $crawler->filter('.description p')->count()
                ? $crawler->filter('.description p')->text()
                : null;

            $url_image = $crawler->filter('.product-image img')->count()
                ? $crawler->filter('.product-image img')->attr('src')
                : null;
        }
        catch (\Exception $e){
            return response()->json([
                'result' => false,
                'msg' => 'Error al interpretar datos del producto.',
                'error' => $e->getMessage()
            ], 500);
        }

        if (!$name || !$url_image) {
            return response()->json([
                'result' => false,
                'msg' => 'No se encontró información del producto con este código.'
            ], 404);
        }

        try {
            $product = Product::create([
                'code' => $validated['code'],
                'name' => $name,
                'description' => $description ?? 'Sin descripción',
                'url_image' => $url_image,
                'stock' => $validated['stock'],
                'price' => $validated['price'],
                'supplier_id' => $validated['supplier_id'],
                'category_id' => $request->category_id ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'msg' => 'Error interno al agregar el producto.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'result' => true,
            'msg' => "Producto agregado correctamente",
            'data' => $product
        ], 201);
    }

    public function getProducts(Request $request){
        # Query params
        $productID = $request->query('product_id');
        $supplierID = $request->query('supplier_id');

        # With product param product_id: http://localhost:8000/api/checkProductsExistence?product_id=1
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

        # With product param supplier_id: http://localhost:8000/api/checkProductsExistence?supplier_id=1
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

    public function createCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string|max:50',
            'category_name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'msg' => 'Error de validación',
                'data' => $validator->errors()
            ]);
        }

        $category = Categories::create($validator->validated());
        return response()->json([
            'result' => true,
            'msg' => 'Categoria creada exitosamente',
        ]);
    }

    public function getAllCategories(){
        $categories = Categories::all();
        return response()->json([
            'result' => true,
            'msg' => 'Categorias obtenidas exitosamente',
            'data' => $categories
        ]);
    }

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
}
