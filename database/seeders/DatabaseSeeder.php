<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Position;
use App\Models\Profile;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
//        Position::factory()->create([
//            'name' => 'regular',
//        ]);
//        Position::factory()->create([
//            'name' => 'moder'
//        ]);
//        Position::factory()->create([
//            'name' => 'admin',
//        ]);
//
//        User::factory(1)->create([
//            'first_name' => 'Dmitry',
//            'email' => 'admin@info.com',
//            'password' => Hash::make(env('ADMIN_PASSWORD')),
//            'phone' => '9009990099',
//            'position_id' => 3,
//            'remember_token' => Str::random(10),
//        ]);
//
//        Profile::factory(1)->create([
//            'user_id' => 1,
//        ]);
//        User::factory(15)->create();

//        $tags = Tag::factory(5)->create();

//        $brands = Brand::factory(20)->create();
//        $categories = Category::factory(10)->create();
//        foreach ($brands as $brand) {
//            $categoriesIds = $categories->random(rand(1, 4))->pluck('id');
//            $brand->categories()->attach($categoriesIds);
//        }
        Category::factory(10)
            ->has(Brand::factory(rand(1, 10)))
            ->create();
        Brand::factory(10)
            ->has(Category::factory(rand(1, 5)))
            ->create();
    }
}
