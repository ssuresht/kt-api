<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('internship_post_id')->constrained('internship_posts');
            $table->boolean('is_read')->default(0);
            $table->tinyInteger('super_power_review')->comment(
                '1: leadership',
                '2: boldness',
                '3: external mind',
                '4: creativity',
                '5: collaborative mind',
                '6: discreet',
                '7: internal mind',
                '8: logical thinking'
            )->nullable();
            $table->longText('super_power_comment')->nullable();
            $table->tinyInteger('growth_idea_review')->comment(
                '1: leadership',
                '2: boldness',
                '3: external mind',
                '4: creativity',
                '5: collaborative mind',
                '6: discreet',
                '7: internal mind',
                '8: logical thinking'
            )->nullable();
            $table->longText('growth_idea_comment')->nullable();
            $table->timestamp('posted_month');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
}
