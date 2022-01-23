<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Comment;
use App\Models\Like;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        for ($i = 0; $i < 1000; $i++) {
            $comment = [];
            for ($j = 0; $j < 100; $j++) {
                $date = $faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null);
                $comment[] = [
                    'user_id' => rand(2, 1000),
                    'post_id' => rand(1, 9274),
                    'content' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
            Comment::insert($comment);

            Like::insert([
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date
                ],
                [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date
                ]
            ]);
        }
    }
}
