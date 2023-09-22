<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Role;
use App\Models\User;
use App\Models\Premission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (config('permission.default_roles') as $role){
            Role::create(['name'=>$role]);
        }

        User::create([
            'name'=>'رسول شبانی',
            'phone' => '9357594939',
            'password' => Hash::make('password')
        ])->assignRole('Super Admin');

        $this->call(CategorySeeder::class);
        $this->call(StoreSeeder::class);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
