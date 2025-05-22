<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     
    public function run()
    {
        $admin = User::create([
            'firstname' => 'Admin',
            'lastname' => 'Aya',
            'email' => 'aya2001syy@gmail.com',
            'password' => Hash::make('1234567890'),
            'age' => 23,
            'location' => 'Head Office',
            'phone' => '1234567890',
            'gender' => 'female',
        ]);

        // Attach the role
        Role::create([
            'user_id' => $admin->id,
            'name' => 'admin',
        ]);
    }
  
}
