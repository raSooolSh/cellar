<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CategoriesController extends Controller
{
    public function getImage(Request $request)
    {
        $request->validate([
            'path' => ['required'],
            'width' => ['integer','min:50', 'max:1920'],
            'height' => ['integer','min:50', 'max:1080']
        ]);

        $width = $request->width ? $request->width : 300;
        $height = $request->height ? $request->height : 400;
        if (Storage::disk('public')->exists('/categories/' . $request->path)) {
            $fileName = mb_substr($request->path, 0, mb_strpos($request->path, '.'));
            $fileExtension = mb_substr($request->path, mb_strpos($request->path, '.') + 1, 3);
            $newFileName = $fileName . "-" . $width . "x" . $height . "." . $fileExtension;


            if (Storage::disk('public')->exists('/categories/' . $newFileName)) {
                return response()->file(public_path('/storage/categories/').$newFileName);
            } else {
                $image = Image::make(public_path('storage/categories/' . $request->path))->resize($width, $height);
                $path = storage_path('app/public/categories/' . $newFileName);
                $image->save($path);
                return response()->file(public_path('/storage/categories/').$newFileName);
            }
        } else {
            throw new HttpException(404,'Not found.');
        }
    }
}
