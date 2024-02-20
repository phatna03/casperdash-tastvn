<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Food;
use App\Models\FoodCategory;
use App\Models\Ingredient;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    //testing
//    User::factory(5)->create();
//    Restaurant::factory(15)->create();
//    FoodCategory::factory(10)->create();
//    Food::factory(50)->create();
//    Ingredient::factory(100)->create();

    //users
    DB::table('users')->insert([
      [
        'name' => 'Admin',
        'email' => 'admin@casperdash.io',
        'password' => Hash::make('casperdash'),
        'role' => 'superadmin',
      ],
      [
        'name' => fake()->name(),
        'email' => 'adm001@mailinator.com',
        'password' => Hash::make('mailinator'),
        'role' => 'admin',
      ],
      [
        'name' => fake()->name(),
        'email' => 'adm002@mailinator.com',
        'password' => Hash::make('mailinator'),
        'role' => 'admin',
      ],
      [
        'name' => fake()->name(),
        'email' => 'moderator001@mailinator.com',
        'password' => Hash::make('mailinator'),
        'role' => 'moderator',
      ],
      [
        'name' => fake()->name(),
        'email' => 'moderator002@mailinator.com',
        'password' => Hash::make('mailinator'),
        'role' => 'moderator',
      ],
    ]);

    //restaurants
    $items = [
      '[R] Cargo HCM', '[R] Cargo Hanoi',
      '[R] Deli HCM', '[R] Deli Hanoi',
    ];
    foreach ($items as $item) {
      Restaurant::create([
        'name' => $item,
      ]);
    }

    //food category
    $items = [
      '[FC] BBQ and Grilling', '[FC] Casseroles', '[FC] Meats', '[FC] Pasta',
      '[FC] Pizza', '[FC] Rice and Beans', '[FC] Soups and Stews', '[FC] Salads',
      '[FC] Stir-Fry',

    ];
    foreach ($items as $item) {
      FoodCategory::create([
        'name' => $item,
      ]);
    }
  }
}
