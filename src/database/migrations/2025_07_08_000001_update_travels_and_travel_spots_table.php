<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // travelsテーブルのカラム追加
        Schema::table('travels', function (Blueprint $table) {
            $table->integer('visibility')->default(0)->after('end_date'); // デフォルト: 非公開
            $table->integer('locationCategory')->default(0)->after('visibility'); // デフォルト: 国内
            $table->integer('prefecture')->nullable()->after('locationCategory');
            $table->integer('country')->nullable()->after('prefecture');
        });

        // travel_spotsテーブルのカラムNULL許容化
        Schema::table('travel_spots', function (Blueprint $table) {
            $table->double('latitude')->nullable()->change();
            $table->double('longitude')->nullable()->change();
        });
    }

    public function down(): void
    {
        // travelsテーブルのカラム削除
        Schema::table('travels', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'locationCategory', 'prefecture', 'country']);
        });

        // travel_spotsテーブルのカラムNULL不可に戻す
        Schema::table('travel_spots', function (Blueprint $table) {
            $table->double('latitude')->nullable(false)->change();
            $table->double('longitude')->nullable(false)->change();
        });
    }
};
