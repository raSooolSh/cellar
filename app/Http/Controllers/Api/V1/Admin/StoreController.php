<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\Stores\AddNewStoreEvent;
use App\Events\Stores\DeleteStoreEvent;
use App\Events\Stores\EditStoreEvent;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreController extends ApiController
{
    public function index()
    {
        return $this->successResponse(['stores' => Store::orderBy('name')->get()], 200);
    }

    public function store(Request $request){
        $request->validate([
            'name'=>['required',Rule::unique('stores','name')],
        ]);

        $store =Store::create([
            'name'=>$request->name,
        ]);

        broadcast(new AddNewStoreEvent($store))->toOthers();
        return $this->successResponse(['store' => $store], 201,'Store created successfully.');
    }

    public function update(Request $request , Store $store){
        $request->validate([
            'name'=>['required',Rule::unique('stores','name')->ignore($store->id)],
        ]);

        $store->update([
            'name'=>$request->name,
        ]);

        broadcast(new EditStoreEvent(Store::find($store->id)))->toOthers();
        return $this->successResponse(['store' => Store::find($store->id)], 200,'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        if ($store->delete()) {
            broadcast(new DeleteStoreEvent($store->id))->toOthers();
            return $this->successResponse([], 200);
        };
    }
}
