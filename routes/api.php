<?php

use Pusher\Pusher;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\V1\RosterController;
use App\Http\Controllers\Api\V1\ProductsController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\Admin\StoreController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\CategoriesController  as AdminCategoriesController;

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

Broadcast::routes(['middleware'=>['auth:sanctum']]);

// images
Route::get('categories/img', [CategoriesController::class, 'getImage'])->name('categories.image');
Route::get('products/img', [ProductsController::class, 'getImage'])->name('products.image');
Route::get('users/img', [AuthController::class, 'getImage'])->name('users.image');
Route::get('/products/rename',function(){
    $products = Product::all();
    foreach($products as $product){
        $brand = substr($product->name,0,strpos($product->name,'-'));
        $productName = substr($product->name,strpos($product->name,'-')+1);
        $newName = $productName.'-'.$brand;
        dd($newName);
        $product->name = $newName;
        $product->save();
    }
});

Route::prefix('/v1')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout')->middleware(['auth:sanctum']);
    Route::get('user', [AuthController::class, 'user'])->name('user.info')->middleware(['auth:sanctum']);

    // usually
    Route::get('/products',[ProductsController::class,'index'])->name('products.index');
    Route::get('/roster',[RosterController::class,'index'])->name('roster.index');
    Route::post('/roster',[RosterController::class,'store'])->name('roster.store');
    Route::post('/roster/set-zero/{product}',[RosterController::class,'setZero'])->name('roster.set-zero');
    Route::post('/roster/extract/{product}',[RosterController::class,'extract'])->name('roster.extract');
    Route::post('/roster/extract-all/{product}',[RosterController::class,'extractAll'])->name('roster.extract-all');


    // admin
    Route::prefix('/admin')->name('admin.')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        // users
        Route::prefix('/users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index')->middleware('role:Super Admin|Store Keeper');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('role:Super Admin');
            Route::get('/{user}', [UserController::class, 'edit'])->name('edit')->middleware('role:Super Admin');
            Route::patch('/{user}', [UserController::class, 'update'])->name('update')->middleware('role:Super Admin');
        });

        // categories
        Route::prefix('/categories')->name('categories.')->group(function () {
            Route::get('/', [AdminCategoriesController::class, 'index'])->name('index');
            Route::post('/create', [AdminCategoriesController::class, 'store'])->name('store')->middleware('role:Super Admin|Store Keeper');
            Route::patch('/{category}', [AdminCategoriesController::class, 'update'])->name('update')->middleware('role:Super Admin|Store Keeper');
            Route::delete('/{category}', [AdminCategoriesController::class, 'destroy'])->name('destroy')->middleware('role:Super Admin|Store Keeper');
        });

        // stores
        Route::prefix('/stores')->name('stores.')->group(function () {
            Route::get('/', [StoreController::class, 'index'])->name('index');
            Route::post('/create', [StoreController::class, 'store'])->name('store')->middleware('role:Super Admin|Store Keeper');
            Route::patch('/{store}', [StoreController::class, 'update'])->name('update')->middleware('role:Super Admin|Store Keeper');
            Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy')->middleware('role:Super Admin|Store Keeper');
        });

        // products
        Route::prefix('/products')->name('products.')->group(function () {
            Route::get('/', [AdminProductController::class, 'index'])->name('index')->middleware('role:Super Admin|Store Keeper');
            Route::post('/create', [AdminProductController::class, 'store'])->name('store')->middleware('role:Super Admin|Store Keeper');
            Route::get('/{product}', [AdminProductController::class, 'edit'])->name('edit')->middleware('role:Super Admin|Store Keeper');
            Route::patch('/{product}', [AdminProductController::class, 'update'])->name('update')->middleware('role:Super Admin|Store Keeper');
            Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('destroy')->middleware('role:Super Admin|Store Keeper');
        });
    });
});
