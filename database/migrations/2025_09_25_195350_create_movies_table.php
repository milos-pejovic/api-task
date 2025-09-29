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
            $table->integer("tmdb_id");
            $table->string("original_language")->nullable();
            $table->string("backdrop_path")->nullable();
            $table->string("original_title")->nullable();
            $table->text("overview")->nullable();
            $table->float("popularity", 10, 5)->nullable();
            $table->string("poster_path")->nullable();
            $table->date("release_date")->nullable();
            $table->string("title");
            $table->float("vote_average");
            $table->integer("vote_count");
            $table->boolean("has_details")->default(0);
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
