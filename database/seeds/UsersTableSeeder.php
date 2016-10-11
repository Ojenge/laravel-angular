<?php

use Illuminate\Database\Seeder;
use App\User;


// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
      $faker = Faker\Factory::create();
        // TestDummy::times(20)->create('App\Post');
        //
        foreach(range(1,5) as $index)
        {
            User::create([
                'name' => $faker->userName,
                'email' =>$faker->email,
                'password' =>bcrypt('secret')
            ]);
        }
    }
}
