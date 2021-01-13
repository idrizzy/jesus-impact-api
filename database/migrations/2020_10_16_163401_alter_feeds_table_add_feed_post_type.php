<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFeedsTableAddFeedPostType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->enum('feedPostType', ['feed', 'shared'])->nullable()->default('feed');
            $table->unsignedBigInteger('feed_id')->nullable();
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
        Schema::table('feeds', function (Blueprint $table) {
            //
        });
    }
}
