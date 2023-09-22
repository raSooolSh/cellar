<?php

namespace App\Models;

use App\Models\Store;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $guarded=[];
    public $appends =['inRoster'];

    public function store() {
        return $this->belongsTo(Store::class);
    }
    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function rosters(){
        return $this->belongsToMany(Roster::class,'roster_product')->withPivot('quantity','user_id','status');
    }
    public function getInRosterAttribute(){
        $roster =  Roster::where('status',1)->where('created_at','>',Carbon::yesterday()->addHour()->toDateTimeString())->first();
        return $roster ? RosterProduct::where('roster_id',$roster->id)->where('product_id',$this->id)->first() : null;
    }
}
