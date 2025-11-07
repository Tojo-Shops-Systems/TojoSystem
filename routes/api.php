<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AccountController;
use App\Http\Controllers\Sales\SuppliersController;
use App\Http\Controllers\Sales\ProductsController;
use App\Http\Controllers\BranchController;

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
    Route::post('/personalData', [AccountController::class, 'registerPerson']);
    Route::post('/register', [AccountController::class, 'register']);
    Route::post('/login', [AccountController::class, 'login']);
    Route::post('/identifyPerson', [AccountController::class, 'identifyPerson']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AccountController::class, 'logout']);
    });
});

# Just the CEO can do this
Route::middleware(['auth:sanctum', 'verifyUserType:CEO'])->group(function () {
    Route::post('/createBranch', [BranchController::class, 'createBranch']);
});


Route::middleware('auth:sanctum')->group(function (){
    Route::post('/suppliers/register', [SuppliersController::class, 'registerSupplier']);
    Route::get('/suppliers/{id?}', [SuppliersController::class, 'getSuppliers']);
    Route::post('/products/registerProduct', [ProductsController::class, 'registerProduct']);
    Route::get('/products', [ProductsController::class, 'getProducts']);
});