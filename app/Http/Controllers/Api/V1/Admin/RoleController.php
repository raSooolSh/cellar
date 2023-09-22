<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

class RoleController extends ApiController
{
    public function index(){
        return $this->successResponse(['roles'=>Role::all()],200);
    }
}
