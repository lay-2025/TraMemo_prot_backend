<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => '山田 太郎',
                'email' => 'yamada@example.com',
                'password' => Hash::make('password'),
                'provider' => null,
                'provider_id' => null,
                'avatar_url' => null,
                'bio' => '旅行好きなエンジニア',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '佐藤 花子',
                'email' => 'sato@example.com',
                'password' => Hash::make('password'),
                'provider' => 'google',
                'provider_id' => 'sato_google_id',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'bio' => '自然が大好き',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
