<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductsController extends ApiController
{
    public function index(Request $request)
    {
        $query = Product::query();
        $query->join('categories', 'categories.id', '=', 'products.category_id')
            ->select('products.name', 'products.barcode', 'products.category_id', 'products.carton_contains', 'products.id', 'products.image', 'products.store_id', 'products.quantity', 'categories.name as category_name');
        
        if ($request->has('search') && $request->search != '') {
            $searchItems = explode(' ', $request->search);
            foreach ($searchItems as $search) {
                $query->Where('products.name', 'LIKE', "%$search%");
            }
            if (count($searchItems) == 1) {
                $query->orWhere('products.barcode', 'LIKE', "%$request->search%");
            }
        }

        if ($request->has('categories') && !(empty($request->categories) || is_null($request->categories))) {
            $query->whereIn('products.category_id', explode(',',$request->categories));
        }

        $query->orderBy('products.updated_at','desc');

        // return $query->first();
        return $this->successResponse([
            'products' => ProductResource::collection($query->with(['store', 'category'])->paginate(1)),
            'meta' => ProductResource::collection($query->with(['store', 'category'])->paginate(1))->response()->getData()->meta,
            'links' => ProductResource::collection($query->with(['store', 'category'])->paginate(1))->response()->getData()->links,
        ], 200);
    }


    public function getImage(Request $request)
    {
        $request->validate([
            'path' => ['required'],
            'width' => ['integer', 'min:50', 'max:1920'],
            'height' => ['integer', 'min:50', 'max:1080']
        ]);

        $width = $request->width ? $request->width : 400;
        $height = $request->height ? $request->height : 400;
        if (Storage::disk('public')->exists('/products/' . $request->path)) {
            $reversePath = strrev($request->path);
            $fileName = strrev(mb_substr($reversePath, mb_strpos($reversePath, '.') + 1));
            $fileExtension = strrev(mb_substr($reversePath, 0, mb_strpos($reversePath, '.')));
            $newFileName = $fileName . "-" . $width . "x" . $height . "." . $fileExtension;


            if (Storage::disk('public')->exists('/products/' . $newFileName)) {
                return response()->file(public_path('/storage/products/') . $newFileName);
            } else {
                $image = Image::make(public_path('storage/products/' . $request->path))->resize($width, $height);
                $path = storage_path('app/public/products/' . $newFileName);
                $image->save($path);
                return response()->file(public_path('/storage/products/') . $newFileName);
            }
        } else {
            throw new HttpException(404, 'Not found.');
        }
    }
}
