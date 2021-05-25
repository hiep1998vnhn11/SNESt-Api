<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Info;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Spatie\Permission\Models\Role;

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
            $date = $faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null);

            Post::insert([
                'user_id' => rand(2, 5000),
                'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
