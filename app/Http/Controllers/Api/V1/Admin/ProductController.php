<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\Products\AddProductsEvent;
use App\Events\Products\DeleteProductsEvent;
use App\Events\Products\EditProductsEvent;
use App\Events\Products\UpdateProductsEvent;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends ApiController
{
    public function index(Request $request)
    {
        return $this->successResponse([
            'products' => Cache::get('products',[]),
        ], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('products', 'name')],
            'barcode' => ['integer'],
            'category' => ['required', 'integer', 'exists:categories,id'],
            'store' => ['required', 'integer', 'exists:stores,id'],
            'carton_contains' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:0'],
            'image' => ['image', 'mimes:png,jpg', 'max:4096'],
        ]);
        if ($request->has('image')) {
            $fileName = uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('/products/' . "$request->name/", $fileName, 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'barcode' => $request->barcode,
            'category_id' => $request->category,
            'store_id' => $request->store,
            'carton_contains' => $request->carton_contains,
            'quantity' => $request->quantity,
            'image' => $request->has('image') ? $fileName : 'default.jpg'
        ]);

        Cache::put('products',ProductResource::collection(Product::query()->with(['store', 'category'])->get()));
        broadcast(new AddProductsEvent($product));
        return $this->successResponse(['product' => new ProductResource($product->load(['store', 'category']))], 201, 'Product created successfully');
    }

    public function edit(Product $product)
    {
        return $this->successResponse([
            'product' => new ProductResource($product->load(['store', 'category'])),
        ], 200);
    }

    public function update(Product $product, Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('products', 'name')->ignore($product->id)],
            'barcode' => ['integer'],
            'category' => ['required', 'integer', 'exists:categories,id'],
            'store' => ['required', 'integer', 'exists:stores,id'],
            'carton_contains' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:0'],
            'image' => ['image', 'mimes:png,jpg', 'max:4096'],
        ]);


        if ($request->has('image')) {
            if (Storage::disk('public')->directoryExists('/products/' . $product->name)) {
                Storage::disk('public')->deleteDirectory('/products/' . $product->name);
            }
            $fileName = uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('/products/' . "$request->name/", $fileName, 'public');
        }

        if ($request->name != $product->name) {
            if (Storage::disk('public')->directoryExists('/products/' . $product->name)) {
                Storage::disk('public')->move('/products/' . $product->name, '/products/' . $request->name);
            }
        }


        $product->update([
            'name' => $request->name,
            'barcode' => $request->barcode,
            'store_id' => $request->store,
            'category_id' => $request->category,
            'carton_contains' => $request->carton_contains,
            'quantity' => $request->quantity,
            'image' => $request->has('image') ? $fileName : $product->image
        ]);

        Cache::put('products',ProductResource::collection(Product::query()->with(['store', 'category'])->get()));

        broadcast(new EditProductsEvent(Product::find($product->id)))->toOthers();

        return $this->successResponse(['product' => new ProductResource(Product::find($product->id)->load(['store', 'category']))], 200, 'Product updated successfully');
    }

    public function destroy(Product $product)
    {

        if (Storage::disk('public')->directoryExists('/products/' . $product->name)) {
            Storage::disk('public')->deleteDirectory('/products/' . $product->name);
        }

        if ($product->delete()) {
            Cache::put('products',ProductResource::collection(Product::query()->with(['store', 'category'])->get()));
            broadcast(new DeleteProductsEvent($product->id))->toOthers();
            return $this->successResponse(['product' => $product->load(['store', 'category'])], 200, 'Product deleted successfully.');
        }
    }
}
