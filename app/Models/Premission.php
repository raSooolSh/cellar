<?php

namespace App\Models;

use \Spatie\Permission\Models\Permission as ModelsPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Premission extends ModelsPermission
{
    use HasFactory;
}
