<?php

namespace Database\Seeders;

use App\Models\Info;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

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
        for ($i = 0; $i < 1000; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $firstName = $faker->firstName($gender);
            $lastName = $faker->lastName;
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $firstName . ' ' . $lastName,
                'slug' => Str::slug($firstName . ' ' . $lastName),
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('123456'), // password
                'phone_number' => $faker->phoneNumber,
                'url' => $faker->uuid,
            ]);
            $user->assignRole($viewer);
            Info::insert([
                'user_id' => $user->id,
                'gender' => $gender,
                'birthday' => $faker->dateTimeBetween($startDate = '-45 years', $endDate = '-10 years', $timezone = null),
                'live_at' => $faker->city,
                'locale' => rand(0, 1) ? 'vi' : 'en',
                'from' => $faker->country,
                'show_live_at' => 1,
                'show_from' => 1,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
        }
    }
}
