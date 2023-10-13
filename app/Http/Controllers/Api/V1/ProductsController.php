<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductsController extends ApiController
{
    public function index(Request $request)
    {
        return $this->successResponse([
            'products' => Cache::get('products',[])
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
