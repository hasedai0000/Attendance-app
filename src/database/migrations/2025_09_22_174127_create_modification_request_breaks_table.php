<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModificationRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modification_request_breaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('modification_request_id');
            $table->time('requested_start_time');
            $table->time('requested_end_time')->nullable();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('modification_request_id')->references('id')->on('modification_requests')->onDelete('cascade');

            // インデックス
            $table->index('modification_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modification_request_breaks');
    }
}
