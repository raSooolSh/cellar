<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\ApiController;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'phone' => ['required', 'unique:users','integer','digits:10','regex:/9\d{9}/'],
            'password' => ['required','string','min:6', 'confirmed'],
            'role' => ['required',"exists:roles,id"]
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole(Role::find($request->role)->getRawOriginal('name'));

        return $this->successResponse($user, Response::HTTP_CREATED, 'User created successfully');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('phone', 'password'), true)) {

            return $this->successResponse(['user' => Auth::user()], Response::HTTP_OK, 'Logged in successfully');
        };

        throw ValidationException::withMessages(['phone' => 'اطلاعات کاربری یافت نشد']);
    }

    public function user()
    {
        return $this->successResponse([
            'user' => new UserResource(auth()->user()),
            'role' => auth()->user()->roles
        ], 200);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return $this->successResponse([], Response::HTTP_OK, 'logged out in successfully');
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
        if (Storage::disk('public')->exists('/users/' . $request->path)) {
            $reversePath = strrev($request->path);
            $fileName = strrev(mb_substr($reversePath, mb_strpos($reversePath, '.') + 1));
            $fileExtension = strrev(mb_substr($reversePath, 0, mb_strpos($reversePath, '.')));
            $newFileName = $fileName . "-" . $width . "x" . $height . "." . $fileExtension;

            if (Storage::disk('public')->exists('/users/' . $newFileName)) {
                return response()->file(public_path('/storage/users/') . $newFileName);
            } else {
                $image = Image::make(public_path('storage/users/' . $request->path))->resize($width, $height);
                $path = storage_path('app/public/users/' . $newFileName);
                $image->save($path);
                return response()->file(public_path('/storage/users/') . $newFileName);
            }
        } else {
            throw new HttpException(404, 'Not found.');
        }
    }
}
