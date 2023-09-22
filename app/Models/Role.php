<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ModelsRole;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends ModelsRole
{
    use HasFactory;

    public function name(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                switch ($value) {
                    case 'Super Admin':
                        return 'ادمین';
                    case 'Store Keeper':
                        return 'انبار دار';
                    case 'Seller':
                        return 'فروشنده';
                }
            }
        );
    }
}
