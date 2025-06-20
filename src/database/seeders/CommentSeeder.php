<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('comments')->insert([
            [
                'user_id' => 2,
                'trip_id' => 1,
                'content' => '素敵な写真ですね！私も先月京都に行きましたが、紅葉はまだ早かったです。タイミングが良かったですね。',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'trip_id' => 1,
                'content' => '伏見稲荷大社の千本鳥居、何度見ても圧巻ですよね。次回は東福寺にも行ってみたいと思います。おすすめのスポットありがとうございます！',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
