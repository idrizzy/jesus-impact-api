<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 500);
            $table->string('description', 500)->nullable();
            $table->string('image')->nullable();
            $table->enum('category', ['closed', 'unclosed'])->nullable()->default('unclosed');
            $table->timestamps();
        });

        Schema::table('feeds', function (Blueprint $table) {
            $table->enum('feedType', ['personal', 'community'])->default('personal');
            $table->unsignedBigInteger('community_id')->nullable();
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
        });

        Schema::create('community_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('community_id');
            $table->enum('status', ['pending', 'active'])->nullable()->default('pending');
            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('communities');
    }
}
