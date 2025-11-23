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
    Route::post('/personalData', [AccountController::class, 'registerPerson']);
    Route::post('/register', [AccountController::class, 'register']);
    Route::post('/login', [AccountController::class, 'login']);
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
Route::post('/register-branch-in-pi', [BranchController::class, 'registerBranchInPI']);

# Just the CEO can do this
Route::middleware(['auth:sanctum', 'verifyUserType:CEO,RH'])->group(function () {
    Route::post('/cloud/create-branch', [BranchController::class, 'createCloudBranch']);
});

# Just the Boss can do this
Route::middleware(['auth:sanctum', 'BossIdentify:Boss'])->group(function () {
    Route::post('/boss/createBranch', [BranchController::class, 'createBranch']);
    Route::patch('/boss/assignBranch', [BranchController::class, 'assignBranch']);
    Route::get('/boss/getBranch', [BranchController::class, 'getBranch']);
    Route::delete('/boss/productDeregister', [ProductsController::class, 'productDeregister']);
});

# Just for the employees on the shop
Route::middleware('auth:sanctum', 'ExclusiveEmployees:Boss,Employee')->group(function (){
    Route::post('/suppliers/register', [SuppliersController::class, 'registerSupplier']);
    Route::get('/suppliers/{id?}', [SuppliersController::class, 'getSuppliers']);
    Route::post('/products/registerProduct', [ProductsController::class, 'registerProduct']);
    Route::get('/products', [ProductsController::class, 'getProducts']);
    Route::post('/purchaseInShop', [TicketController::class, 'purchaseInShop']);
});


# Just for customers
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/createOrders', [OrderController::class, 'store']);
});

// Endpoint que el SCRIPT DE PYTHON llama para dar el "aviso"
Route::patch('/cabinet/notify-status/{ticketId}', [GabineteController::class, 'recibirNotificacionHardware']);