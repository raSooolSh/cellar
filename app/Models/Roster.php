<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function products(){
        return $this->belongsToMany(Product::class,'roster_product')->withPivot('quantity','user_id','status');
    }
}
