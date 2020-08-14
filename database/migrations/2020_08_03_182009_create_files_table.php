<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('filename',500)->nullable();
            $table->timestamps();
        });

        Schema::create('feed_file', function (Blueprint $table) {
            $table->unsignedBigInteger('feed_id');
            $table->unsignedBigInteger('file_id');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('feed_id')->references('id')->on('feeds')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
        Schema::dropIfExists('feed_file');
    }
}
