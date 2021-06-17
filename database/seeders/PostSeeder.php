<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Info;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10000; $i++) {
            $date = $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = null);
            Post::insert([
                'user_id' => rand(2, 1000),
                'uid' => rand(1000000000, 9999999999),
                'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
