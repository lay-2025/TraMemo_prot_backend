<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->text('avatar_url')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        // Travels
        Schema::create('travels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // Travel Spots
        Schema::create('travel_spots', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('travel_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('travel_id');
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->integer('day_number')->nullable();
            $table->date('visit_date')->nullable();
            $table->time('visit_time')->nullable();
            $table->string('name');
            $table->double('latitude');
            $table->double('longitude');
            // 順番（ルート表示用）
            $table->integer('order_index')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });

        // Photos
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id');
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->unsignedBigInteger('travel_spot_id');
            $table->foreign('travel_spot_id')->references('id')->on('travel_spots')->onDelete('cascade');
            $table->text('url');
            $table->text('thumbnail_url')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();
        });

        // Tags
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Travel_Tag (pivot)
        Schema::create('travel_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('travel_id');
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['travel_id', 'tag_id']);
        });

        // Likes
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('travel_id');
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->timestamps();
        });

        // Favorites
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('travel_id');
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->timestamps();
        });

        // Comments
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('travel_id');
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('travel_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('travel_spots');
        Schema::dropIfExists('travels');
        Schema::dropIfExists('users');
    }
};
