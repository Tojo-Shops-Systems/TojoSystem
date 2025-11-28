<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AccountController;
use App\Http\Controllers\Sales\SuppliersController;
use App\Http\Controllers\Sales\ProductsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Sales\TicketController;
use App\Http\Controllers\Users\GabineteController;
use App\Http\Controllers\Users\CustomersController;
use App\Http\Controllers\Users\OrderController;
use App\Http\Controllers\Sales\ProductsCloudController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

# Registro y logueo de usuarios de empleado o usuario
Route::prefix('auth')->group(function () {
    # Employee routes
    Route::post('/existsPersonInCloud', [AccountController::class, 'existsPersonInCloud']); # Solo es para verificar si el encargado ya existe en la nube
    Route::post('/cloud/personalData', [AccountController::class, 'registerPerson']);
    # Este sera exclusivo para el registro local
    Route::post('/register', [AccountController::class, 'register']);
    # Cuando haga el registro local de la cuenta, esta se registrara igual en la nube
    # La logica lo tendra la app de IOS
    Route::post('/cloud/register', [AccountController::class, 'register']);
    # Como solo se puede hacer ese registro en local, se ejecutara la api de la nube /auth/cloud/register para registrar al usuario en la nube
    Route::post('/login', [AccountController::class, 'login']);
    # Se identificara si los datos personales se encuentran en la nube o local
    Route::post('/cloud/identifyPerson', [AccountController::class, 'identifyPerson']);
    Route::post('/identifyPerson', [AccountController::class, 'identifyPerson']);
    
    # Customers routes
    Route::post('/registerCustomer', [CustomersController::class, 'register']);
    Route::post('/loginCustomer', [CustomersController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AccountController::class, 'logout']);
    });
});

Route::get('/cloud/get-branch-by-key/{key}', [BranchController::class, 'getBranchByActivationKey']);
Route::patch('/cloud/activate-branch-key/{id}', [BranchController::class, 'activateBranchKey']);

# La app de IOS solo hara esta api para registrar la sucursal en la base de datos local con la llave de activacion
Route::post('/register-branch-in-pi', [BranchController::class, 'registerBranchInPI']);

Route::post('/boss/productsExistence', [ProductsCloudController::class, 'checkProductsExistence']);
Route::get('/boss/getAllCategories', [ProductsCloudController::class, 'getAllCategories']);

# Just the CEO can do this
Route::middleware(['auth:sanctum', 'verifyUserType:CEO,RH'])->group(function () {
    Route::post('/cloud/create-branch', [BranchController::class, 'createCloudBranch']);
    # El CEO o RH da de alta al encargado en la nube, y el encargado verificara su alta con la api /cloud/identifyPerson
    Route::post('/boss/personalData', [AccountController::class, 'registerPerson']); # Solo es para registrar al encargado en la nube con datos personales
    Route::post('/cloud/register', [AccountController::class, 'register']); # Solo es para registrar al encargado en la nube con correo y contraseña
});

# Just the Boss can do this
Route::middleware(['auth:sanctum', 'BossIdentify:Boss'])->group(function () {
    Route::post('/boss/createBranch', [BranchController::class, 'createBranch']);
    Route::patch('/boss/assignBranch', [BranchController::class, 'assignBranch']);
    Route::get('/boss/getBranch', [BranchController::class, 'getBranch']);
    Route::delete('/boss/productDeregister', [ProductsController::class, 'productDeregister']);
    # El jefe es el que da de alta a la persona localmente los datos personales
    # Se ejecutara la api de la nube /auth/cloud/personalData para registrar a la persona en la nube
    # Esa logica la llevara la aplicacion de IOS
    Route::post('/boss/personalData', [AccountController::class, 'registerPerson']);

    # APIS para alta de productos
    #Antes de registrar el producto, se debe verificar que no exista en la base de datos local
    Route::post('/products/checkProductsExistence', [ProductsController::class, 'getProducts']);
    # Luego si no esta local verifica en la nube
    Route::post('/cloud/products/checkProductsExistence', [ProductsController::class, 'checkProductsExistence']);
    # Si no existe, se registrara el producto
    # En caso de que exista en la nube, se registrara el producto localmente al endpoint /products/registerProduct
    Route::post('/products/registerProduct', [ProductsController::class, 'registerProduct']);
    Route::post('/products/fastRegisterProduct', [ProductsController::class, 'registerProduct']);
    # Despues de agregar el producto, se agregara en la nube
    Route::post('/products/registerProductInCloud', [ProductsCloudController::class, 'registerProduct']);
    # APIs para alta de proveedores
    Route::post('/suppliers/register', [SuppliersController::class, 'registerSupplier']);
});

# Just for the employees on the shop
Route::middleware('auth:sanctum', 'ExclusiveEmployees:Boss,Employee')->group(function (){
    Route::get('/suppliers/{id?}', [SuppliersController::class, 'getSuppliers']);
    Route::get('/products', [ProductsController::class, 'getProducts']);
    Route::post('/purchaseInShop', [TicketController::class, 'purchaseInShop']);
});


# Just for customers
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/createOrders', [OrderController::class, 'store']);
});

# APIs generales para clientes o visitantes de la web
Route::get('/customers/products', [ProductsCloudController::class, 'getProducts']);
Route::get('/customers/getAllCategories', [ProductsCloudController::class, 'getAllCategories']);
Route::get('/customers/getBranchHasSpecifyProduct', [ProductsCloudController::class, 'getBranchHasSpecifyProduct']);
Route::get('/customers/getBranchesData', [BranchController::class, 'getBranchesData']);

// Endpoint que el SCRIPT DE PYTHON llama para dar el "aviso"
Route::patch('/cabinet/notify-status/{ticketId}', [GabineteController::class, 'recibirNotificacionHardware']);