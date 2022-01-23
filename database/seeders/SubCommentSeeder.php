<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Like;
use App\Models\SubComment;

class SubCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        for ($i = 0; $i < 100000; $i++) {
            $date = $faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null);
            SubComment::insert([
                [
                    'user_id' => rand(2, 1000),
                    'comment_id' => rand(1, 100000),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'comment_id' => rand(1, 100000),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'comment_id' => rand(1, 100000),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'comment_id' => rand(1, 100000),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'comment_id' => rand(1, 100000),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'comment_id' => rand(1, 100000),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
            ]);

            Like::insert([
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 100000),
                    'status' => rand(1, 5),
                    'likeable_type' => 'App\Models\Comment',
                    'created_at' => $date,
                    'updated_at' => $date,
                ],
            ]);
        }
    }
}
