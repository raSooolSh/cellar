<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Carbon\Carbon;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CategoryResource;
use App\Events\Categories\EditCategoryEvent;
use App\Events\Categories\AddNewCategoryEvent;
use App\Events\Categories\DeleteCategoryEvent;

class CategoriesController extends ApiController
{
    public function index(Request $request)
    {
        if ($request->input('search') != '') {
            return $this->successResponse([
                'categories' => CategoryResource::collection(Category::where('name', 'Like', "%$request->search%")->orderBy('name', $request->sort)->get()),
            ], 200);
        }
        return $this->successResponse([
            'categories' => CategoryResource::collection(Category::orderBy('name', $request->sort)->get())
        ], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:categories'],
            'image' => ['image', 'mimes:png,jpg', 'max:4096'],
        ]);

        if ($request->has('image')) {
            $fileName = uniqid($request->name . '-') . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('/categories/'. "$request->name/", $fileName, 'public');
        }



        $category = Category::create([
            'name' => $request->name,
            'image' => $request->has('image') ? $fileName : 'default.jpg'
        ]);

        broadcast(new AddNewCategoryEvent($category))->toOthers();

        return $this->successResponse(['category' => new CategoryResource($category)], 201, 'category created successfully');
    }

    public function edit(Category $category)
    {
        return $this->successResponse([
            'category' => new CategoryResource($category),
        ], 200);
    }


    public function update(Category $category, Request $request)
    {
        $request->validate([
            'name' => ['required', Rule::unique('categories')->ignore($category->id)],
            'image' => ['image', 'mimes:png,jpg', 'max:4096'],
        ]);

        $category->name = $request->name;

        if ($request->has('image')) {
            if (Storage::disk('public')->directoryExists('/categories/' . $category->name)) {
                Storage::disk('public')->deleteDirectory('/categories/' . $category->name);
            }
            $fileName = uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('/categories/' . "$request->name/", $fileName, 'public');
            $category->image = $fileName;
        }

        if ($request->name != $category->name) {
            if (Storage::disk('public')->directoryExists('/categories/' . $category->name)) {
                Storage::disk('public')->move('/categories/' . $category->name, '/categories/' . $request->name);
            }
        }


        $category->save();

        broadcast(new EditCategoryEvent(Category::find($category->id)))->toOthers();

        return $this->successResponse(['category'=>new CategoryResource(Category::find($category->id))], 200, 'category updated successfully');
    }

    public function destroy(Category $category)
    {
        if (Storage::disk('public')->directoryExists('/categories/' . $category->name)) {
            Storage::disk('public')->deleteDirectory('/categories/' . $category->name);
        }
        if ($category->delete()) {
            broadcast(new DeleteCategoryEvent($category->id))->toOthers();

            $this->successResponse(['category'=>$category], 200, 'Category deleted successfully.');
        }
    }
}
