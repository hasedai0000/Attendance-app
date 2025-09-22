<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendance_id');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');

            // インデックス
            $table->index('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('breaks');
    }
}
