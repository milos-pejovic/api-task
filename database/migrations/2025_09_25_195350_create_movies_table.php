<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer("tmdb_id")->unique();
            $table->string("original_language")->nullable();
            $table->string("backdrop_path")->nullable();
            $table->string("original_title")->nullable();
            $table->text("overview")->nullable();
            $table->float("popularity", 10, 5)->nullable();
            $table->string("poster_path")->nullable();
            $table->date("release_date")->nullable();
            $table->string("title")->default('N/A');
            $table->float("vote_average")->default(0.0);
            $table->integer("vote_count")->default(0);
            $table->boolean("has_details")->default(0);

            // Additional data from basic "details" API route
            $table->string("budget")->nullable();
            $table->string("homepage")->nullable();
            $table->string("origin_country")->nullable();
            $table->string("revenue")->nullable();
            $table->string("status", 32)->nullable();
            $table->text("tagline")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
