<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Products\EditProductEvent;
use Carbon\Carbon;
use App\Models\Roster;
use App\Models\Product;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\RosterProduct;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductResource;
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
                broadcast(new EditProductEvent(Product::find($productInRoster->product_id)))->toOthers();
            } else {
                $productInRoster->update([
                    'quantity' => $request->quantity,
                    'user_id' => auth()->user()->id,
                    'status' => 0
                ]);
                broadcast(new EditProductEvent(Product::find($productInRoster->product_id)))->toOthers();
                return $this->successResponse(['item' => RosterProduct::find($productInRoster->id)], 200, 'Product updated in roster successfully');
            }
        } else {
            $productInRoster = RosterProduct::create([
                'roster_id' => $roster->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => auth()->user()->id,
            ]);
            broadcast(new EditProductEvent(Product::find($productInRoster->product_id)))->toOthers();
            return $this->successResponse(['item' => $productInRoster], 201, 'Product added to roster successfully');
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
                broadcast(new EditProductEvent(Product::find($productInRoster->product_id)))->toOthers();
                return $this->successResponse([], 200, 'Product deleted from roster successfully');
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
                broadcast(new EditProductEvent(Product::find($productInRoster->product_id)))->toOthers();
                return $this->successResponse([], 200, 'Product updated successfully');
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
                broadcast(new EditProductEvent(Product::find($product->id)))->toOthers();
                return $this->successResponse([], 200, 'Product updated successfully');
            } else {
                return $this->errorResponse("Product doesn't exist in roster.", 404);
            }
        } else {
            return $this->errorResponse("Roster doesn't exist", 500);
        }
    }
}
