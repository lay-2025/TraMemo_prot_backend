<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TripSpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('trip_spots')->insert([
            [
                'trip_id' => 1,
                'day_number' => 1,
                'visit_date' => '2025-04-01',
                'visit_time' => '09:00:00',
                'name' => '京都駅',
                'latitude' => 34.985849,
                'longitude' => 135.758766,
                'order_index' => 1,
                'memo' => '待ち合わせ場所',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 1,
                'visit_date' => '2025-04-01',
                'visit_time' => '12:00:00',
                'name' => '金閣寺',
                'latitude' => 35.039705,
                'longitude' => 135.729243,
                'order_index' => 2,
                'memo' => '観光名所',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
