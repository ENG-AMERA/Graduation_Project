<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $categories = ['Skin Care','Body Care','Perfume' ,'Hair Mist','Herbs','Others'];

        foreach ($categories as $type) {
            Category::create([
                'type' => $type,

            ]);
        }
    }
}
