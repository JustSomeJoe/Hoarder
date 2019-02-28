<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Posts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigInteger('id')->primary()->index();
            $table->string('post_id');
            $table->boolean('done')->default(0)->index();
            $table->string('domain')->nullable()->index();
            $table->text('title');
            $table->text('self_text')->nullable();
            $table->text('url')->nullable();
            $table->boolean('over_18')->index();
            $table->bigInteger('subreddit_id')->index();
            $table->bigInteger('author_id')->index();
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
