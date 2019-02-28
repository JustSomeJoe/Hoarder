<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Dupes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Dupes', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('parent')->index();
            $table->string('name')->index();
            $table->integer('subreddit_id')->nullable()->index();
            $table->integer('author_id')->nullable()->index();
            $table->string('post')->nullable()->index();
            $table->datetime('created_utc')->nullable();
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
        //
    }
}
