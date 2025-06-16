<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('photos')->insert([
            [
                'trip_id' => 1,
                'trip_spot_id' => 1,
                'url' => 'https://example.com/photo1.jpg',
                'thumbnail_url' => 'https://example.com/thumb1.jpg',
                'caption' => '京都駅到着',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
