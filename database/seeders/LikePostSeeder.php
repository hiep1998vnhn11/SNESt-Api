<?php

namespace Database\Seeders;

use App\Models\Like;
use Illuminate\Database\Seeder;

class LikePostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date = now();
        for ($i = 0; $i < 5000; $i++) {
            $likes = [];
            for ($j = 0; $j < 200; $j++) {
                $likes[] = [
                    'user_id' => rand(2, 1000),
                    'likeable_id' => rand(1, 10000),
                    'status' => rand(1, 7),
                    'likeable_type' => 'App\Models\Post',
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
            Like::insert($likes);
        }
    }
}
