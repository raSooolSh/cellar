<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Roster;
use App\Models\Product;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\RosterProduct;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductResource;
use App\Events\Products\EditProductsEvent;
use App\Events\ProductsInRoster\EditProductInRoster;
use App\Events\ProductsInRoster\AddProductInRosterEvent;
use App\Events\ProductsInRoster\EditProductInRosterEvent;
use App\Events\ProductsInRoster\DeleteProductInRosterEvent;

class RosterController extends ApiController
{
    public function index()
    {
        $roster = Roster::where('status', 1)->first();
        return $this->successResponse([
            'products' => $roster ? ProductResource::collection($roster->products()->with(['store', 'category'])->get()) : [],
            'date' => $roster ? Jalalian::fromDateTime($roster->created_at)->format('%A, %d %B %y') : Jalalian::fromCarbon(Carbon::now())->format('%A, %d %B %y')
        ], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0']
        ]);

        if (!$roster = Roster::where('status', 1)->first()) {
            $roster = Roster::create(['status' => 1]);
        }

        if ($productInRoster = RosterProduct::where('product_id', $request->product_id)->where('roster_id', $roster->id)->first()) {
            if ($request->quantity == 0) {
                $productInRoster->delete();
                broadcast(new EditProductsEvent(Product::find($productInRoster->product_id)));
                return $this->successResponse(['item' => new ProductResource(Product::find($productInRoster->product_id))], 200, 'Product deleted from roster successfully');
            } else {
                $productInRoster->update([
                    'quantity' => $request->quantity,
                    'user_id' => auth()->user()->id,
                    'status' => 0
                ]);
                broadcast(new EditProductsEvent(Product::find($productInRoster->product_id)));
                return $this->successResponse(['item' => new ProductResource(Product::find($productInRoster->product_id))], 200, 'Product updated in roster successfully');
            }
        } else {
            $productInRoster = RosterProduct::create([
                'roster_id' => $roster->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => auth()->user()->id,
            ]);
            broadcast(new EditProductsEvent(Product::find($productInRoster->product_id)));
            return $this->successResponse(['item' => new ProductResource(Product::find($productInRoster->product_id))], 201, 'Product added to roster successfully');
        }
    }

    public function setZero(Product $product)
    {
        $product->update([
            'quantity' => 0,
        ]);

        $roster = Roster::where('status', 1)->first();

        if ($roster) {
            $productInRoster = RosterProduct::where('product_id', $product->id)->where('roster_id', $roster->id)->where('status', 0)->first();
            if ($productInRoster and $productInRoster->delete()) {
                Cache::put('products',ProductResource::collection(Product::query()->with(['store', 'category'])->get()));
                broadcast(new EditProductsEvent(Product::find($productInRoster->product_id)));
                return $this->successResponse(['item' => new ProductResource(Product::find($productInRoster->product_id))], 200, 'Product deleted from roster successfully');
            } else {
                return $this->errorResponse("Product doesn't exist in roster.", 404);
            }
        } else {
            return $this->errorResponse("Roster doesn't exist", 500);
        }
    }

    public function extractAll(Product $product)
    {
        $product->update([
            'quantity' => 0,
        ]);

        $roster = Roster::where('status', 1)->first();

        if ($roster) {
            $productInRoster = RosterProduct::where('product_id', $product->id)->where('roster_id', $roster->id)->where('status', 0)->first();
            if ($productInRoster and $productInRoster->update(['status' => 1])) {
                Cache::put('products',ProductResource::collection(Product::query()->with(['store', 'category'])->get()));
                broadcast(new EditProductsEvent(Product::find($productInRoster->product_id)));
                return $this->successResponse(['item' =>new ProductResource(Product::find($productInRoster->product_id)->load(['store','category']))], 200, 'Product updated successfully');
            } else {
                return $this->errorResponse("Product doesn't exist in roster.", 404);
            }
        } else {
            return $this->errorResponse("Roster doesn't exist", 500);
        }
    }

    public function extract(Product $product)
    {
        $roster = Roster::where('status', 1)->first();

        if ($roster) {
            $productInRoster = RosterProduct::where('product_id', $product->id)->where('roster_id', $roster->id)->where('status', 0)->first();
            if ($productInRoster and $productInRoster->update(['status' => 1])) {
                $product = Product::find($productInRoster->product_id);
                $product->update([
                    'quantity' => $product->quantity - $productInRoster->quantity,
                ]);
                Cache::put('products',ProductResource::collection(Product::query()->with(['store', 'category'])->get()));
                broadcast(new EditProductsEvent(Product::find($product->id)))->toOthers();
                return $this->successResponse(['item' =>new ProductResource(Product::find($product->id)->load(['store','category']))], 200, 'Product updated successfully');
            } else {
                return $this->errorResponse("Product doesn't exist in roster.", 404);
            }
        } else {
            return $this->errorResponse("Roster doesn't exist", 500);
        }
    }
}
