<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'چیپس و پفک'=>'chips.png',
            'رب و کنسرو' => 'conserves.png',
            'شوینده ها'=>'detergent.png',
            'پوشک ها'=>'diaper.png',
            'کیسه زباله و پلاستیک'=>'garbage.png',
            'دستکش ها'=>'gloves.png',
            'آبمیوه و شربت'=>'juice.png',
            'ماکارونی ها'=>'macaroni.png',
            'نوار بهداشتی'=>'napkin.png',
            'سس ها'=>'souce.png',
            'چای و نسکافه'=>'tea.png',
            'دستمال کاغذی و توالت'=>'tissuePaper.png',
            'محصولات بهداشتی'=>'cosmetics.png',
            'خوراکی (کیک و بیسکوبیت)'=>'cakes.png',
            'غیره'=>'salt.png'
        ];

        foreach($categories as $name=>$image){
            Category::create([
                'name'=>$name,
                'image'=>$image
            ]);
        }
    }
}
