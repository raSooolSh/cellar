<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use Illuminate\Validation\Rule;

class UserController extends ApiController
{
    public function index(Request $request)
    {
        if ($request->input('search') != '') {
            return $this->successResponse([
                'users' => UserResource::collection(User::where('name', 'Like', "%$request->search%")->with('roles')->orderBy('name', $request->sort)->paginate(20)),
                'meta' => UserResource::collection(User::where('name', 'Like', "%$request->search%")->with('roles')->orderBy('name', $request->sort)->paginate(20))->response()->getData()->meta,
                'links' => UserResource::collection(User::where('name', 'Like', "%$request->search%")->with('roles')->orderBy('name', $request->sort)->paginate(20))->response()->getData()->links,
            ], 200);
        }
        return $this->successResponse([
            'users' => UserResource::collection(User::orderBy('name', $request->sort)->with('roles')->paginate(20)),
            'meta' => UserResource::collection(User::where('name', 'Like', "%$request->search%")->with('roles')->orderBy('name', $request->sort)->paginate(20))->response()->getData()->meta,
            'links' => UserResource::collection(User::where('name', 'Like', "%$request->search%")->with('roles')->orderBy('name', $request->sort)->paginate(20))->response()->getData()->links,
        ], 200);
    }

    public function edit(Request $request, User $user)
    {
        return $this->successResponse([
            'user' => new UserResource($user),
        ], 200);
    }

    public function update(Request $request,User $user)
    {
        $request->validate([
            'name' => ['required','string','nullable'],
            'phone' => ['required',Rule::unique('users')->ignore($user->id),'integer','digits:10','regex:/9\d{9}/'],
            'password' => ['nullable','min:6','confirmed'],
            'role' => ['required', "exists:roles,id"]
        ]);

        $user->name =$request->name;
        $user->phone =$request->phone;
        if($request->has('password') && !is_null($request->password)){
            $user->password = Hash::make($request->password);
        }
        if($user->roles()->first()->id != $request->role){
            $user->removeRole($user->roles()->first());
            $user->assignRole(Role::find($request->role)->getRawOriginal('name'));
        }
        $user->save();
        return $this->successResponse($user, Response::HTTP_OK, 'User updated successfully');
    }

    public function destroy(User $user){
        $user->delete();

        $this->successResponse([],200,'User deleted successfully.');
    }
}
