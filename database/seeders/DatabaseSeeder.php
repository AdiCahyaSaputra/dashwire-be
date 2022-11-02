<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {
    User::create([
      'name' => "Adi Cahya",
      'email' => 'adics@gmail.com',
      'password' => bcrypt('hehe1234')
    ]);

    User::create([
      'name' => "Cahyadi Putra",
      'email' => 'cahya@gmail.com',
      'password' => bcrypt('hehe1234')
    ]);

    User::create([
      'name' => "Putra Adi",
      'email' => 'saputra@gmail.com',
      'password' => bcrypt('hehe1234')
    ]);

    $this->call([
      TableInfoSeeder::class
    ]);
  }
}
