<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternshipFeaturePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internship_feature_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_feature_id')->constrained('internship_features');
            $table->foreignId('internship_post_id')->constrained('internship_posts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internship_feature_posts');
    }
}
