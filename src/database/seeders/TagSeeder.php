<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tags')->insert([
            ['id' => 1, 'name' => '自然', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => '寺院', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
