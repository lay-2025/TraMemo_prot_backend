<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('trips')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'title' => '京都旅行',
                'description' => '春の京都を満喫',
                'start_date' => '2025-04-01',
                'end_date' => '2025-04-03',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'title' => '北海道一周',
                'description' => '夏の北海道ドライブ旅',
                'start_date' => '2025-08-10',
                'end_date' => '2025-08-20',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
