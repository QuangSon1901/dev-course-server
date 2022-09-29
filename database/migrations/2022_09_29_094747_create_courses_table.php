<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('quantity_minimum')->nullable(false);
            $table->integer('quantity_maxnimum')->nullable(false);
            $table->bigInteger('subject_id')->nullable(false);
            $table->bigInteger('teacher_id')->nullable(false);
            $table->bigInteger('room_id')->nullable(false);
            $table->bigInteger('time_frame_id')->nullable(false);
            $table->bigInteger('week_day_id')->nullable(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
