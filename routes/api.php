<?php

use Carbon\Carbon;
use Pusher\Pusher;
use App\Models\User;
use App\Models\Roster;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\RosterProduct;
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
route::get('cronjob/daily',function(Request $request){
    if($request->password = env('CRON_JOB_PASSWORD','password')){
        $rosters = Roster::where('status', '1')->get();
            $lastRoster = Roster::where('status', '1')->orderBy('id', 'desc')->first();
            $pendingProducts = RosterProduct::where('status', 0)->where('roster_id', $lastRoster->id)->get();

            foreach ($rosters as $roster) {
                $roster->status = 0;
                $roster->save();
            };

            $newRoster = Roster::create([
                'status' => 1
            ]);

            foreach ($pendingProducts as $rosterProduct) {
                RosterProduct::create([
                    'product_id' => $rosterProduct->product_id,
                    'roster_id' => $newRoster->id,
                    'status' => 0,
                    'user_id' => $rosterProduct->user_id,
                    'quantity' => $rosterProduct->quantity,
                    'created_at' => $rosterProduct->created_at,
                    'updated_at' => $rosterProduct->updated_at,
                ]);
            }
    }
});

route::get('cronjob/monthly',function(Request $request){
    if($request->password = env('CRON_JOB_PASSWORD','password')){
        $rosters = Roster::where('created_at', '<', Carbon::now()->subMonths(6))->get();
            foreach ($rosters as $roster) {
                $roster->delete();
            };
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
