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
            // Day 1
            [
                'trip_id' => 1,
                'day_number' => 1,
                'visit_date' => '2023-10-15',
                'visit_time' => '10:00:00',
                'name' => '京都駅到着',
                'latitude' => 34.985849,
                'longitude' => 135.758766,
                'order_index' => 1,
                'memo' => 'みんなでここに集合',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 1,
                'visit_date' => '2023-10-15',
                'visit_time' => '11:30:00',
                'name' => '東福寺で紅葉鑑賞',
                'latitude' => 34.969219,
                'longitude' => 135.773799,
                'order_index' => 2,
                'memo' => '紅葉がとても綺麗だった',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 1,
                'visit_date' => '2023-10-15',
                'visit_time' => '14:00:00',
                'name' => '伏見稲荷大社参拝',
                'latitude' => 34.967146,
                'longitude' => 135.772695,
                'order_index' => 3,
                'memo' => '千本鳥居が圧巻だった',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 1,
                'visit_date' => '2023-10-15',
                'visit_time' => '17:00:00',
                'name' => '旅館チェックイン',
                'latitude' => 35.039705,
                'longitude' => 135.729243,
                'order_index' => 4,
                'memo' => '荷物を置いて一休み',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Day 2
            [
                'trip_id' => 1,
                'day_number' => 2,
                'visit_date' => '2023-10-16',
                'visit_time' => '09:00:00',
                'name' => '金閣寺見学',
                'latitude' => 35.039705,
                'longitude' => 135.729243,
                'order_index' => 1,
                'memo' => '金色がまぶしかった',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 2,
                'visit_date' => '2023-10-16',
                'visit_time' => '13:00:00',
                'name' => '龍安寺の石庭見学',
                'latitude' => 35.034634,
                'longitude' => 135.718828,
                'order_index' => 2,
                'memo' => '静かで落ち着く場所',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 2,
                'visit_date' => '2023-10-16',
                'visit_time' => '15:30:00',
                'name' => '嵐山散策',
                'latitude' => 35.009414,
                'longitude' => 135.670246,
                'order_index' => 3,
                'memo' => '竹林の道が印象的',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 2,
                'visit_date' => '2023-10-16',
                'visit_time' => '18:00:00',
                'name' => '京都駅周辺で夕食',
                'latitude' => 34.985849,
                'longitude' => 135.758766,
                'order_index' => 4,
                'memo' => 'みんなで美味しいご飯を食べた',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Day 3
            [
                'trip_id' => 1,
                'day_number' => 3,
                'visit_date' => '2023-10-17',
                'visit_time' => '08:30:00',
                'name' => '清水寺参拝',
                'latitude' => 34.994856,
                'longitude' => 135.785046,
                'order_index' => 1,
                'memo' => '舞台からの景色が最高',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 3,
                'visit_date' => '2023-10-17',
                'visit_time' => '11:00:00',
                'name' => '八坂神社参拝',
                'latitude' => 35.003611,
                'longitude' => 135.778889,
                'order_index' => 2,
                'memo' => '歴史を感じる神社',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 3,
                'visit_date' => '2023-10-17',
                'visit_time' => '14:00:00',
                'name' => '祇園散策',
                'latitude' => 35.003707,
                'longitude' => 135.778749,
                'order_index' => 3,
                'memo' => '町並みが風情ある',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trip_id' => 1,
                'day_number' => 3,
                'visit_date' => '2023-10-17',
                'visit_time' => '17:30:00',
                'name' => '鴨川沿いで夕食',
                'latitude' => 35.011636,
                'longitude' => 135.768029,
                'order_index' => 4,
                'memo' => '川の景色を見ながらゆっくり食事',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
