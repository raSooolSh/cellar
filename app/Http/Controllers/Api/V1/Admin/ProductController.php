<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\Products\DeleteProductEvent;
use App\Events\Products\EditProductEvent;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends ApiController
{
    public function index(Request $request)
    {
        $request->sort ?: $request['sort'] = 'name';
        $request->sortDirection ?: $request['sortDirection'] = 'asc';


        $query = Product::query();
        // $query->join('categories', 'categories.id', '=', 'products.category_id')
        //     ->select('products.name', 'products.barcode', 'products.category_id', 'products.carton_contains', 'products.id', 'products.image', 'products.store_id', 'products.quantity', 'categories.name as category_name');
        //     $query->join('stores', 'stores.id', '=', 'products.store_id')
        //     ->select('products.name as name', 'products.barcode as barcode', 'products.category_id', 'products.carton_contains', 'products.id', 'products.image', 'products.store_id', 'products.quantity', 'stores.name as store_name');
        if ($request->has('search') && $request->search != '') {
            $searchItems = explode(' ', $request->search);
            $query->where(function ($query) use ($searchItems, $request) {
                foreach ($searchItems as $search) {
                    $query->where('name', 'LIKE', "%$search%");
                }
                if (count($searchItems) == 1) {
                    $query->orWhere('products.barcode', 'LIKE', "%$request->search%");
                }
            });
        }


        if ($request->has('category') && (!(empty($request->category) || is_null($request->category)))) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('store') && (!(empty($request->store) || is_null($request->store)))) {
            $query->where('store_id', $request->store);
        }

        switch ($request->sort) {
            case 'store':
                $products =  ProductResource::collection($query->with(['store', 'category'])->orderBy('store.name', $request->sortDirection)->paginate(20));
                break;
            case 'category':
                $products =  ProductResource::collection($query->with(['store', 'category'])->orderBy('category.name', $request->sortDirection)->paginate(20));
                break;
            default:
                $products =  ProductResource::collection($query->with(['store', 'category'])->orderBy('name', $request->sortDirection)->paginate(20));
        }
        return $this->successResponse([
            'products' => $products,
            'meta' => ProductResource::collection($query->with(['store', 'category'])->paginate(20))->response()->getData()->meta,
            'links' => ProductResource::collection($query->with(['store', 'category'])->paginate(20))->response()->getData()->links,
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

        return $this->successResponse(['product' => new ProductResource($product)], 201, 'Product created successfully');
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

        broadcast(new EditProductEvent(Product::find($product->id)))->toOthers();

        return $this->successResponse(['product' => new ProductResource(Product::find($product->id)->load(['store', 'category']))], 200, 'Product updated successfully');
    }

    public function destroy(Product $product)
    {

        if (Storage::disk('public')->directoryExists('/products/' . $product->name)) {
            Storage::disk('public')->deleteDirectory('/products/' . $product->name);
        }

        if ($product->delete()) {
            broadcast(new DeleteProductEvent($product->id))->toOthers();
            return $this->successResponse(['product' => $product], 200, 'Product deleted successfully.');
        }
    }
}
