<?php

namespace Database\Seeders;

use App\Models\Info;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $viewer = Role::find(1);
        for ($i = 0; $i < 100; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('123456'), // password
                'phone_number' => $faker->phoneNumber,
                'url' => rand(10 ** 10, 10 ** 11),
            ]);
            $user->assignRole($viewer);
            Info::create([
                'user_id' => $user->id,
                'gender' => rand(0, 1) ? 'male' : 'female',
            ]);
        }
    }
}
