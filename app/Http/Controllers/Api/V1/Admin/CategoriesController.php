<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\Categories\AddNewCategoryEvent;
use App\Events\Categories\DeleteCategoryEvent;
use App\Events\Categories\EditCategoryEvent;
use Carbon\Carbon;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Resources\CategoryResource;

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
            'image' => ['required', 'image', 'mimes:png,jpg', 'max:5120'],
        ]);

        $fileName = uniqid($request->name . '-') . '.' . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->storeAs('/categories/', $fileName, 'public');

        $category = Category::create([
            'name' => $request->name,
            'image' => $fileName
        ]);

        broadcast(new AddNewCategoryEvent($category));

        return $this->successResponse(['category' => $category], 201, 'category created successfully');
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
            'image' => ['image', 'mimes:png,jpg', 'max:5120'],
        ]);

        $category->name = $request->name;

        if ($request->has('image')) {
            $fileName = uniqid($request->name . '-') . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('/categories/', $fileName, 'public');

            $category->image = $fileName;
        }


        $category->save();

        broadcast(new EditCategoryEvent(Category::find($category->id)));

        return $this->successResponse($category, 200, 'category updated successfully');
    }

    public function destroy(Category $category)
    {
        if($category->delete()){
            broadcast(new DeleteCategoryEvent($category->id));

            $this->successResponse([], 200, 'Category deleted successfully.');
        }

    }
}
