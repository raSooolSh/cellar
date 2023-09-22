<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            'همکف',
            'طبقه 1',
            'پارکینگ',
            'زیرزمین'
        ];

        foreach($stores as $store){
            Store::create([
                'name'=>$store,
            ]);
        }
    }
}
